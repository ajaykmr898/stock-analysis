<?php


namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;

class StockController
{
    public $start;
    public function __construct() {
        $this->start = mktime(0, 0, 0, 1, 1, 2015);
    }

    public function getData() {
        $all = DB::select('select * from company where active = ?', [1]);
        $data = DB::select('SELECT company.id, company.code, company.name,
        STR_TO_DATE(MAX(stock.date), "%Y-%m-%d") as date, UNIX_TIMESTAMP(STR_TO_DATE(MAX(stock.date), "%Y-%m-%d")) as timestamp
        FROM company, stock
        WHERE company.id=stock.company_id and active = ?
        GROUP BY company.id, company.code, company.name
        ORDER BY company.id', [1]);
         if (count($data) == 0) {
             foreach ($all as $line) {
                 $line->date = '-';
                 $line->timestamp = $this->start;
                 array_push($data, $line);
             }
         } else {
             $keys = array_map( function ($v) {
                 return $v->id;
             }, $data);
             foreach ($all as $k => $v) {
                 if (!in_array($v->id, $keys)) {
                     $temp = $all[$k];
                     $temp->date = '-';
                     $temp->timestamp = $this->start;
                     array_push($data, $temp);
                 }
             }
         }
        return $data;
    }

    public function fetchData($request) {
        if ($request->get('type') == 'all-old' || $request->get('type') == 'all-new') {
            $companies = $this->getData();
        } else {
            $company = new \stdClass();
            $company->id = $request->get('id');
            $company->code = $request->get('code');
            if ($request->get('type') == 'single-old') {
                $company->period = $this->start;
            } else if ($request->get('type') == 'single-new') {
                $company->period = $request->get('timestamp');
            } else {
                return false;
            }
            $companies = [$company];
        }

        if (count($companies) > 0) {
            $filter = "history";
            $frequency = "1d";
            foreach ($companies as $company) {
                $symbol = $company->code;
                $company_id = $company->id;
                $period1 = (isset($company->period)) ? $company->period : $company->timestamp;
                $period2 = time();
                $diff = $period2 - $period1;
                $fullDays = floor($diff/(60*60*24));
                if ($fullDays > 0) {
                    $url = "https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v2/get-historical-data?frequency=$frequency&filter=$filter&period1=$period1&period2=$period2&symbol=$symbol";
                    $response = $this->curl($url);
                    if ($response['status']) {
                        $this->saveData($response['data'], $company_id, date('Y-m-d', $period1), $request->get('type'));
                    }
                }
            }
        }
        return true;
    }

    public function saveData($json, $company_id, $last_date, $delete) {
        $data = json_decode($json, true);
        if (strpos($delete, "old") != false) {
            DB::delete("delete from stock where company_id = ?", [$company_id]);
        }
        foreach ($data['prices'] as $line) {
            if (isset($line['open']) && $last_date < date('Y-m-d', $line['date'])) {
                DB::insert('insert into stock (id, company_id, `date`, `open`, `close`, high, low, volume)
                    values (null, :company_id, :date, :open, :close, :high, :low, :volume)',
                    [
                        "company_id" => $company_id,
                        "date" => date("Y-m-d", $line["date"]),
                        "open" => $line["open"],
                        "close" => $line["close"],
                        "high" => $line["high"],
                        "low" => $line["low"],
                        "volume" => $line["volume"]
                    ]
                );
            }
        }
        return true;
    }

    public function curl($url) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "x-rapidapi-host: apidojo-yahoo-finance-v1.p.rapidapi.com",
                "x-rapidapi-key: 4a7b70445fmsh7b7b4ff7f1754bbp1b2a5djsn65ecb07560a3"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return ["status" => false, "data" => $err];
        } else {
            return ["status" => true, "data" => $response];
        }
    }

    public function getDataByCompanyId($request) {
        $id = $request->get('id');
        $day = [];
        for ($i = date('Y', $this->start); $i < date('Y'); $i++) {
            for ($j = 0; $j <= 6; $j++) {
                array_push($day, "'".date("$i-m-d", strtotime("+ $j  days"))."'");
            }
        }
        for ($i = 0; $i < 30; $i++) {
            array_push($day, "'".date("Y-m-d", strtotime("- $i days"))."'");
        }
        $old = [];
        if (count($day) > 0) {
            $days = trim(implode(", ", array_unique($day)));
            $old = DB::select("select id, date, open, close, high, low, volume, (high - low) as hldiff, (close - open) as ocdiff from stock where company_id = :id and date in ($days)", ["id" => $id]);
        }
        $byWeeks = [];
        foreach ($old as $k => $v) {
            $key = date('D', strtotime($v->date));
            if (!array_key_exists($key, $byWeeks)) {
                $byWeeks[$key] = [];
            }
            array_push($byWeeks[$key], $v);
        }

        foreach ($byWeeks as $k1 => $v1) {
            $odiff = 0;
            $cdiff = 0;
            $hdiff = 0;
            $ldiff = 0;
            $volume = 0;
            foreach ($v1 as $k2 => $v2) {
                $odiff += $v2->open;
                $cdiff += $v2->close;
                $hdiff += $v2->high;
                $ldiff += $v2->low;
                $volume += $v2->volume;
            }
            $byWeeks[$k1]['todiff'] = $odiff;
            $byWeeks[$k1]['tcdiff'] = $cdiff;
            $byWeeks[$k1]['modiff'] = ($odiff/count($v1)/count($v1));
            $byWeeks[$k1]['mcdiff'] = ($cdiff/count($v1)/count($v1));
            $byWeeks[$k1]['thdiff'] = $hdiff;
            $byWeeks[$k1]['tldiff'] = $ldiff;
            $byWeeks[$k1]['mhdiff'] = $hdiff/count($v1);
            $byWeeks[$k1]['mldiff'] = $ldiff/count($v1);
            $byWeeks[$k1]['tvolume'] = $volume;
            $byWeeks[$k1]['mvolume'] = $volume/count($v1);
        }

        $data = DB::select("select id, date, open, close, high, low, volume from stock where company_id = :id order by date desc limit 2", ["id" => $id]);
        $final = [];
        //print_r($old);return;
        if (count($data) > 0) {
            $last_day = $data[0];
            $slast_day = isset($data[1]) ? $data[1] : $data[0];
            $size1 = floor(strlen($last_day->volume)/2);
            $size2 = floor($size1/2);
            $delta = $last_day->volume - $slast_day->volume;
            for ($i = 0; $i < $size1 ;$i++ ) {
                $delta = $delta/100;
            }
            $temp = ($size2 == 1) ? 100 : 10;
            $delta = $delta * $temp;

            foreach (["Mon", "Tue", "Wed", "Thu", "Fri"] as $k => $v) {
                if ($delta > $last_day->open) {
                    $delta = 0;
                }
                //$final[$v]['data'] = strlen($last_day->volume). '->' . $size1 . '->' . $size2 . '->' . $delta . '->' . $last_day->open . '->' . $byWeeks[$v]['mcdiff'];
                $final[$v]['open'] = $last_day->open + $byWeeks[$v]['mcdiff'] + $delta;
                $final[$v]['close'] = $last_day->close + $byWeeks[$v]['mcdiff'] + $delta;
            }
        }
        return [$data, $byWeeks, $final];
    }
}
