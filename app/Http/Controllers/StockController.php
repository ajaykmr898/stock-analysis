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
                        $this->saveData($response['data'], $company_id, date('Y-m-d', $period1));
                    }
                }
            }
        }
        return true;
    }

    public function saveData($json, $company_id, $last_date) {
        $data = json_decode($json, true);
        foreach ($data['prices'] as $line) {
            if (isset($line['open']) && $last_date < date('Y-m-d', $line['date'])) {
                DB::insert('insert into stock (id, company_id, `date`, `open`, `close`, high, low, volume)
                    values (null, :company_id, :date, :open, :close, :high, :low, :volume)',
                    [
                        "company_id" => $company_id,
                        "date" => date("Y-m-d H:i:s", $line["date"]),
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
}
