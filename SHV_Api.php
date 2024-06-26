<?php
/**
 * Created by PhpStorm.
 * User: janik
 * Date: 11-Oct-18
 * Time: 10:28
 */

class SHV_Api
{
    private $authorization;
    public static $SHV_API= "https://clubapi-test.handball.ch/rest/v1/";

    public function __construct(string $authorization)
    {
        $this->authorization = $authorization;
    }

    public function getGames(int $teamId)
    {
        $url = self::$SHV_API . "clubs/140336/teams/$teamId/games";
        return json_decode($this->getDataFromApi($url));
    }
    public function getPlayedGames(int $teamId)
    {
        $url = self::$SHV_API . "clubs/140336/teams/$teamId/games?status=played";
        return json_decode($this->getDataFromApi($url));
    }

    public function getTeams(int $club): array {
        $url = self::$SHV_API . "clubs/$club/teams";
        return json_decode($this->getDataFromApi($url));
    }

    private function getDataFromApi(string $url): string {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic $this->authorization",
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
