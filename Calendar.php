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
        $cal_text = "BEGIN:VCALENDAR"
                    . "\nVERSION:2.0"
                    . "\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN"
                    . "\nCALSCALE:GREGORIAN";

        foreach ($this->games as $game) {
            $cal_text = $cal_text . "\nBEGIN:VEVENT"
                    . "\nSUMMARY:" . $this->createSummary($game)
                    . "\nLOCATION:" . $this->createLocation($game)
                    . "\nDESCRIPTION:Allez HBC!"
                    . "\nDTSTART;TZID=Europe/Zurich:" . $this->createStartDate($game)
                    . "\nDTEND;TZID=Europe/Zurich:" . $this->createEndDate($game)
                    . "\nDTSTAMP;TZID=Europe/Zurich:" . $this->createTimeStamp()
                    . "\nUID:" . uniqid()
                    . "\nEND:VEVENT";
        }

        $cal_text = $cal_text . "\nEND:VCALENDAR";

        $fileName = "/cal_export/" . $this->calendarName . ".ics";
        $file = fopen(__DIR__ . $fileName, 'w');
        fwrite($file, $cal_text);
        fclose($file);
        echo "<p>File successfully saved. <a href='"  . $fileName . "' target='_blank'>$fileName</a></p>";

    }
    private function createSummary($game) {
        return $game->teamAName . " vs. " . $game->teamBName;
    }
    private function createStartDate($game): string {
        $startDate = new DateTime($game->gameDateTime);
        return $startDate->format("Ymd\THis\Z");
    }
    private function createEndDate($game): string {
        $endDate = new DateTime($game->gameDateTime, new DateTimeZone('Europe/Zurich'));
        return $endDate->add(new DateInterval('PT2H'))->format("Ymd\THis\Z");
    }
    private function createLocation($game) {
        return $game->venue . ", " . $game->venueAddress . ", " . $game->venueZip . " " . $game->venueCity;
    }
    private function createTimeStamp() {
        $date = new DateTime("now", new DateTimeZone("Europe/Zurich"));
        return $date->format("Ymd\THis\Z");
    }
}