<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $participants = [];
        
        // $games = [
        //     'Orientacininkų kontrolinės + BFP' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017069/rezgru/V16?diena=1', 'rate' => 85, 'timeIndex' => 4],
        //     'Lietuvos orientavimosi sporto bėgte čempionatas sprinte' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017078/rezgru/V16?diena=1', 'rate' => 100, 'timeIndex' => 4],
        //     'Baltic championships' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017067/rezgru/M16E?diena=1', 'rate' => 100, 'timeIndex' => 5],
        // ];  
        
        // $gamesV18 = [
        //     'Orientacininkų kontrolinės + BFP' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017069/rezgru/V18?diena=1', 'rate' => 85, 'timeIndex' => 4],
        //     'Lietuvos orientavimosi sporto bėgte čempionatas sprinte' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017078/rezgru/V18?diena=1', 'rate' => 100, 'timeIndex' => 4],
        //     'Baltic championships' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017067/rezgru/M18E?diena=1', 'rate' => 100, 'timeIndex' => 5],
        // ];  
        // $gamesM18 = [
        //     'Orientacininkų kontrolinės + BFP' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017069/rezgru/M18?diena=1', 'rate' => 85, 'timeIndex' => 4, 'group' => 'M18'],
        //     'Lietuvos čemp. sprintas' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017078/rezgru/M18?diena=1', 'rate' => 100, 'timeIndex' => 4, 'group' => 'M18'],
        //     'Baltic championships' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017067/rezgru/W18E?diena=1', 'rate' => 100, 'timeIndex' => 5, 'group' => 'M18'],
        // ];  
        $gamesM16 = [
            'Orientacininkų kontrolinės + BFP' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017069/rezgru/M16?diena=1', 'rate' => 85, 'timeIndex' => 4, 'group' => 'M16'],
            'Lietuvos čemp. sprintas' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017078/rezgru/M16?diena=1', 'rate' => 100, 'timeIndex' => 4, 'group' => 'M16'],
            'Baltic championships' => ['url' => 'https://dbtopas.lt/takas/lt/varz/2017067/rezgru/W16E?diena=1', 'rate' => 100, 'timeIndex' => 5, 'group' => 'M16'],
        ];  
        foreach ($gamesM16 as $title => $game) {
            $info = $this->getData($game);
            
            foreach ($info as $participant) {
                $participants[$title][$participant[1]] = 
                        [
                            'info' => $participant,
                            'rate' => $this->getRate($participant, $info[0], $game)
                        ];
                } 
            
        }
        
        $this->getAsHtml($participants);
        
        die();
    }
    
    protected function getAsHtml($content, $title = 'Baltic championships')
    {
        echo '<html><table>';
        foreach ($content[$title] as $row) {
            echo '<tr><td>'.$row['info'][1].'</td><td>'.$row['rate'].'</td></tr>';
            
        }
        echo '</table>';
    }
    
    protected function getRate($row, $winner, $game)
    {
        $cof = $game['rate'];
        $timeIndex = $game['timeIndex'];
        
        $time = explode(':', $winner[$timeIndex]);
        
        if (count($time) > 2) {
            $winnerTimeS = $time[0] * 3600 + $time[1] * 60 + $time[2];
        } else {
            $winnerTimeS = $time[0] * 60 + $time[1];
        }
        
        if (in_array($row[$timeIndex], ['dsq', 'dnf'])) {
            $cTime = 0;
            
            return round(0 * $cof, 2);
        } else {
            $time = explode(':', $row[$timeIndex]);
            if (count($time) > 2) {
                $cTime = $time[0] * 3600 + $time[1] * 60 + $time[2];
            } else {
                $cTime = $time[0] * 60 + $time[1];
            }
            
            return round((2 - $cTime/$winnerTimeS) * $cof, 2);
        }
        
    }
    
    protected function getData($game)
    {
        $rows = array();
        $blackList['M18'] = ['Tinteris Benas'];
        $blackList['M16'] = ['Perminas Darius'];
        
            $crawler = new Crawler(file_get_contents($game['url']));
            
            
            $nodeValues = $crawler->filterXPath('//table[@class="tbl"]/tr');
            
            foreach ($nodeValues as $i => $node) {
                
                $tds = array();
                // create crawler instance for result
            
                $crawler = new Crawler($node);
                //iterate again
                foreach ($crawler->filter('td') as $i => $node) {
                   // extract the value
                
                    $tds[] = trim(rtrim($node->nodeValue));

                }
                
                if (empty($tds)) {
                    continue;
                }
                
                
                if ($tds['2'] == 'LTU' && !in_array($tds[1], $blackList[$game['group']])) {
                    $rows[] = $tds;
                }
            }
        
        
        return $rows;
    }
}
