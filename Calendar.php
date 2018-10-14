<?php
/**
 * Created by PhpStorm.
 * User: janik
 * Date: 11-Oct-18
 * Time: 12:17
 */


class Calendar
{
    private $calendarName;
    private $teamId;
    private $games;

    public function __construct(string $teamName, string $teamId)
    {
        $this->calendarName = $teamName;
        $this->teamId = $teamId;
    }
    public function setGames(array $games) {
        $this->games = $games;
    }
    public function save() {
        echo "<p>Saving calendar for $this->calendarName</p>";
        $cal_text = "BEGIN:VCALENDAR \n
                    VERSION:2.0 \n
                    PRODID:-//hacksw/handcal//NONSGML v1.0//EN \n
                    CALSCALE:GREGORIAN \n";

        foreach ($this->games as $game) {
            $cal_text = $cal_text . "BEGIN:VEVENT
                    SUMMARY:" . $this->createSummary($game)
                    . "LOCATION:" . $this->createLocation($game)
                    . "DESCRIPTION:Allez HBC!"
                    . "DTSTART:" . $this->createStartDate($game)
                    . "DTEND:20170116T100000Z" . $this->createEndDate($game)
                    . "DTSTAMP:" . $this->createTimeStamp()
                    . "UID:" . uniqid()
                    . "END:VEVENT";
        }

        $cal_text = $cal_text . "END:VCALENDAR";

        $fileName = "cal_export/" . $this->calendarName . ".ics";
        $file = fopen($fileName, 'w');
        fwrite($file, $cal_text);
        fclose($file);
        echo "<p>File successfully saved. <a href='" . __DIR__  . $fileName . "' target='_blank'>$fileName</a></p>";

    }
    private function createSummary($game) {
        return $game->teamAName . " vs. " . $game->teamBName;
    }
    private function createStartDate($game): string {
        $startDate = new DateTime($game->gameDateTime);
        return $startDate->format("yyyyMMdd'T'HHmmss");
    }
    private function createEndDate($game): string {
        $endDate = new DateTime($game->gameDateTime, new DateTimeZone('Europe/Zurich'));
        return $endDate->add(new DateInterval('PT2H'))->format("yyyyMMdd'T'HHmmss");
    }
    private function createLocation($game) {
        return $game->venue . " \n " . $game->venueAddress . ", " . $game->venueZip . " " . $game->venueCity;
    }
    private function createTimeStamp() {
        $date = new DateTime("now", new DateTimeZone("Europe/Zurich"));
        return $date->format("yyyyMMdd'T'HHmmss");
    }
}