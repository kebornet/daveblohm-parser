<?php

require_once __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

class DaveBlohm
{
    const BASE_URL = 'http://daveblohm.com/bibs/';

    public function __construct()
    {
    }

    public function getActionPage($requestMethod, $relativeUrl,  $formParams = [])
    {
        $client = new Client();

        $formData = [
            'form_params' => $formParams
        ];

        try {
            $response = $client->request($requestMethod, self::BASE_URL . $relativeUrl, $formData);
            return $response->getBody()->getContents();
        } catch (BadResponseException $e) {
            echo $e->getResponse()->getBody();
        }
    }

    public function getInfoBibs()
    {
        $bibsInfo = [];
        $bibsInfoPage = $this->getActionPage('GET', 'default.aspx');

        if (!preg_match_all('/<option value="(.*)">(.+)<\/option>/sU', $bibsInfoPage, $raceTypes)) {
            throw new Exception('Unable to get race types bibs info');
        }

        foreach ($raceTypes[1] as $raceType => $key) {
            $bibsInfo[] = $this->getInfoBibsRaceType($bibsInfoPage, $raceTypes[1][$raceType], $raceTypes[2][$raceType]);
        }

        return $bibsInfo;
    }

    private function getInfoBibsRaceType($bibsInfoPage, $raceTypeNumber, $raceType)
    {
        $raceTypeInfoPage = $this->getPageRaceType($bibsInfoPage, $raceTypeNumber);

        if (preg_match('/<option selected="selected" value="">Select Race<\/option>(.*)<\/select>/sU', $raceTypeInfoPage, $racesInfoHtmlBlock)) {
            if (!preg_match_all('/<option value="(.+)">(.+)<\/option>/sU', $racesInfoHtmlBlock[1], $racesInfo)) {
                throw new Exception('Unable to get race info');
            }
        }

        $bibsRaceType = [];

        foreach ($racesInfo[1] as $raceInfo => $key) {
            $nameRace = $racesInfo[2][$raceInfo];
            $pictureRace = self::BASE_URL . 'assets/images/bibs/' . $racesInfo[1][$raceInfo];
            $bibsRaceType[] = [$raceType, $nameRace, $pictureRace];
        }

        return $bibsRaceType;
    }

    private function getPageRaceType($bibsInfoPage, $raceTypeNumber)
    {
        if (!preg_match('/<input type="hidden" name="__VIEWSTATEGENERATOR" id="__VIEWSTATEGENERATOR" value="(.*)" \/>/Ui', $bibsInfoPage, $viewStateGenerator)) {
            throw new Exception('Unable to get value __VIEWSTATEGENERATOR');
        }

        if (!preg_match('/<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*)" \/>/Ui', $bibsInfoPage, $eventValidation)) {
            throw new Exception('Unable to get value __EVENTVALIDATION');
        }

        if (!preg_match('/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*)" \/>/Ui', $bibsInfoPage, $viewState)) {
            throw new Exception('Unable to get value __VIEWSTATE');
        }

        $formParams = [
            '__EVENTTARGET' => 'cctl00$ContentPlaceHolder1$ddlRaceType',
            '__EVENTARGUMENT' => '',
            '__LASTFOCUS' => '',
            '__VIEWSTATE' => $viewState[1],
            '__VIEWSTATEGENERATOR' => $viewStateGenerator[1],
            '__EVENTVALIDATION' => $eventValidation[1],
            'ctl00$ContentPlaceHolder1$ddlRaceType' => $raceTypeNumber,
            'ctl00$ContentPlaceHolder1$txtNumber' => '',
            'ctl00$ContentPlaceHolder1$txtName' => ''
        ];

        $raceTypeInfoPage = $this->getActionPage('POST', 'default.aspx', $formParams);

        return $raceTypeInfoPage;
    }
}
