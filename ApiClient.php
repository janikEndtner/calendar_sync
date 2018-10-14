<?php
/**
 * Created by PhpStorm.
 * User: janik
 * Date: 11-Oct-18
 * Time: 12:31
 */
require_once 'google-api-php-client-master/google-api-php-client-2.1.3/vendor/autoload.php';

class ApiClient
{
    static $service;
    /**
     * Returns an authorized API client.
     * @return Google_Service_Calendar the authorized client object
     */
    private static function getService() {
        if (ApiClient::$service == null) {
            $client = new Google_Client();
            $client->setApplicationName('Manage HBC Calendars');
            $client->setScopes(Google_Service_Calendar::CALENDAR);
            echo __DIR__ . 'credentials.json';
            $client->setAuthConfig(__DIR__ . 'credentials.json');
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            // Load previously authorized token from a file, if it exists.
            $tokenPath = 'token.json';
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);
            }

            // If there is no previous token or it's expired.
            if ($client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    // Request authorization from the user.
                    $authUrl = $client->createAuthUrl();
                    printf("Open the following link in your browser:\n%s\n", $authUrl);
                    print 'Enter verification code: ';
                    $authCode = trim(fgets(STDIN));

                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);

                    // Check to see if there was an error.
                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception(join(', ', $accessToken));
                    }
                }
                // Save the token to a file.
                if (!file_exists(dirname($tokenPath))) {
                    mkdir(dirname($tokenPath), 0700, true);
                }
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
            ApiClient::$service = new Google_Service_Calendar($client);
        }
        return ApiClient::$service;
    }

    public static function getFutureEvents($calendarId): array {
        $service = ApiClient::getService();
        $now = new DateTime('now', new DateTimeZone('Europe/Zurich'));
        $optParams = array('timeMin' => $now->format('c'));

        return $service->events->listEvents($calendarId, $optParams)->getItems();
    }

    /**
     * Makes a public calendar
     * @param string $calendarName
     * @param string $teamId
     * @return Google_Service_Calendar_Calendar
     * @throws Exception
     */
    public static function makeCalendar(string $calendarName, string $teamId): Google_Service_Calendar_Calendar {
        $service = ApiClient::getService();
        $calendar = new Google_Service_Calendar_Calendar();
        $calendar->setSummary($calendarName);
        $calendar->setDescription("HBC Münsingen $calendarName: Spiele. \nTeamId: $teamId");
        $calendar->setTimeZone("Europe/Zurich");

        // insert calendar
        $createdCalendar = $service->calendars->insert($calendar);

        // setting calendar to public
        $rule = new Google_Service_Calendar_AclRule();
        $scope = new Google_Service_Calendar_AclRuleScope();
        $scope->setType("default");
        $scope->setValue("");
        $rule->setScope($scope);
        $rule->setRole("reader");
        $createdRule = $service->acl->insert($createdCalendar->id, $rule);

        return $createdCalendar;
    }

    public static function getAllCalendars(): array {
        $service = ApiClient::getService();
        return $service->calendarList->listCalendarList()->getItems();
    }

    public static function removeEvent(string $eventId, string $calendarId) {
        $service = ApiClient::getService();
        $service->events->delete($calendarId, $eventId);
    }

    public static function addEvent(string $startDate, string $endDate, string $summary, string $location, string $calendarId) {
        $service = ApiClient::getService();
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $summary,
            'location' => $location,
            'description' => 'Allez HBC!',
            'start' => array(
                'dateTime' => $startDate,
                'timeZone' => 'Europe/Zurich',
            ),
            'end' => array(
                'dateTime' => $endDate,
                'timeZone' => 'Europe/Zurich',
            )
        ));

        return $service->events->insert($calendarId, $event);
    }
}

