<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category\Loader;

use EzAd\Bot\Category\Category;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class NewMediaRetailerLoader
 * @package EzAd\Bot\Category\Loader
 */
class NewMediaRetailerLoader extends AbstractCategoryLoader
{
    /**
     * @return Category[]
     */
    public function loadCategories()
    {
        $domain = $this->domain;
        $client = $this->getClient();

        $catalog = $client->request('GET', 'http://' . $domain . '/catalog');
        $categories = [];

        // "use" is so annoying for PHP closures

        $catalog->filter('ul.dropdown.dropdown-vertical > li')->each(function($li1) use ($domain, &$categories) {
            /** @var Crawler $li1 */
            $anch1 = $li1->filterXPath('./li/a')->first();
            $cat1 = Category::makeNew($domain, null, $anch1->text(), $anch1->link()->getUri());
            $categories[] = $cat1;

            $li1->filterXPath('./li/ul/li')->each(function($li2) use ($domain, &$categories, $cat1) {
                /** @var Crawler $li2 */
                $anch2 = $li2->filterXPath('./li/a')->first();
                $cat2 = Category::makeNew($domain, $cat1, $anch2->text(), $anch2->link()->getUri());
                $cat1->addChild($cat2);
                $categories[] = $cat2;

                $li2->filterXPath('./li/ul/li')->each(function($li3) use ($domain, &$categories, $cat2) {
                    /** @var Crawler $li3 */
                    $anch3 = $li3->filterXPath('./li/a')->first();
                    $cat3 = Category::makeNew($domain, $cat2, $anch3->text(), $anch3->link()->getUri());
                    $cat2->addChild($cat3);
                    $categories[] = $cat3;
                });
            });
        });

        return $categories;
    }

    /**
     * @param $domain
     * @return bool
     */
    public function matches($domain)
    {
        $client = $this->getClient();
        $client->request('GET', 'http://' . $domain . '/catalog');
        $status = $client->getInternalResponse()->getStatus();
        if ( $status != 200 ) {
            return false;
        }

        return strpos($client->getInternalResponse()->getContent(), 'New Media Retailer') !== false;
    }
}

/*
<ul class="dropdown dropdown-vertical">
    <li><a href="http://mygrandrental.com/catalog/78245/contractor">Contractor</a> <span class="divider"></span>
        <ul>
            <li><a href="http://mygrandrental.com/catalog/78409/air-compressor">Air compressor</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78457/portable-compressor">Portable
                            Compressor</a><br/> <span class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78458/air-compressor">Air Compressor</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78464/air-tools">Air Tools</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78410/concrete">Concrete</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78449/cement-mixer">Cement Mixer</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78469/cut-off-saw">Cut off Saw</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78411/construction">Construction</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78443/mini-excavator">Mini Excavator</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78444/skid-steer">Skid Steer</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78446/tractor">Tractor</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78447/tractor-backhoe">Tractor Backhoe</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78448/trencher">Trencher</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78412/floor-care">Floor Care</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78437/floor-finish">Floor Finish</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78438/floor-nailer">Floor Nailer</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78441/tile-stripper">Tile Stripper</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78500/floor-sanders">Floor Sanders</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/93449/floor-sweeper">Floor Sweeper</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78413/generators">Generators</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78414/hand-power-tools">Hand & Power Tools</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78431/power-tools">Power Tools</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78432/roofing">Roofing</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78433/roofing-nailers">Roofing Nailers</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78415/hvac">HVAC</a><br/> <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78429/heaters">Heaters</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78416/lifts">Lifts</a><br/> <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78423/boom">Boom</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78425/push-around-lift">Push Around Lift</a><br/>
                        <span class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78426/scaffolding">Scaffolding</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78427/scissor-lift">Scissor Lift</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78505/material-lifts">Material Lifts</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78417/paint-sprayers">Paint Sprayers</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78418/plumbing">Plumbing</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78419/pressure-washer-pumps">Pressure Washer & Pumps</a><br/>
                <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78421/pressure-washer">Pressure Washer</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78422/pumps">Pumps</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78420/trailers">Trailers</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78461/welders">Welders</a><br/></li>
        </ul>
        <div class="clear"></div>
    </li>
    <li><a href="http://mygrandrental.com/catalog/78246/do-it-yourself">Do-it-yourself</a> <span class="divider"></span>
        <ul>
            <li><a href="http://mygrandrental.com/catalog/78380/concrete">Concrete</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78406/cement-mixer">Cement Mixer</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78510/cut-off-saw">Cut off Saw</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78381/construction">Construction</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78382/floor-care">Floor Care</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78401/floor-finish">Floor Finish</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78405/carpet-extractor">Carpet Extractor</a><br/>
                        <span class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78515/floor-sanders">Floor Sanders</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/93448/floor-sweeper">Floor Sweeper</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78383/hvac">HVAC</a><br/> <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78517/heaters">Heaters</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78384/lifts">Lifts</a><br/> <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78398/ladders">Ladders</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78385/paint-sprayers">Paint Sprayers</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78386/plumbing">Plumbing</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78387/hand-power-tools">Hand & Power Tools</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78392/cut-off-saw">Cut off Saw</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78393/power-tools">Power Tools</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78397/metal-detector">Metal Detector</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/93455/transits-and-levels">Transits and
                            Levels</a><br/> <span class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78388/pressure-washer-pumps">Pressure Washer & Pumps</a><br/>
                <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78390/pressure-washer">Pressure Washer</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78389/automotive">Automotive</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78459/trailers">Trailers</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/93450/sand-blasting-kit">Sand Blasting Kit</a><br/></li>
        </ul>
        <div class="clear"></div>
    </li>
    <li><a href="http://mygrandrental.com/catalog/78247/landscaping">Landscaping</a> <span class="divider"></span>
        <ul>
            <li><a href="http://mygrandrental.com/catalog/78359/grounds-care">Grounds Care</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78363/logsplitter">Logsplitter</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78377/chippershredder">Chipper/Shredder</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78378/blowers">Blowers</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78360/earth-drilling">Earth Drilling</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78376/auger">Auger</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78361/turf-equipment">Turf Equipment</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78369/aerator">Aerator</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78371/tiller">Tiller</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78373/brushcutter">Brushcutter</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78362/saws">Saws</a><br/></li>
        </ul>
        <div class="clear"></div>
    </li>
    <li><a href="http://mygrandrental.com/catalog/78248/party-event">Party & Event</a> <span class="divider"></span>
        <ul>
            <li><a href="http://mygrandrental.com/catalog/78295/inflatables">Inflatables</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78354/bounce-houses">Bounce Houses</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78355/combo-inflatables">Combo Inflatables</a><br/>
                        <span class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78358/interactive-inflatables">Interactive
                            Inflatables</a><br/> <span class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78296/games">Games</a><br/> <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78350/carnival">Carnival</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78297/tentscanopies">Tents/Canopies</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78346/poletension-tents">Pole/Tension Tents</a><br/>
                        <span class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78347/frame-tents">Frame Tents</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78348/globe-lighting">Globe Lighting</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78300/concession">Concession</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78338/popcorn">Popcorn</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78303/tabletop">Tabletop</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78304/furniture">Furniture</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78305/cooking-equipment">Cooking Equipment</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78319/grill">Grill</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78306/dcor">Décor</a><br/> <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78316/archescolumns">Arches/Columns</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78307/heatingcooling">Heating/Cooling</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78313/patio-heaters">Patio Heaters</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78308/linens">Linens</a><br/></li>
        </ul>
        <div class="clear"></div>
    </li>
    <li><a href="http://mygrandrental.com/catalog/78249/wedding">Wedding</a> <span class="divider"></span>
        <ul>
            <li><a href="http://mygrandrental.com/catalog/78260/tentscanopies">Tents/Canopies</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78290/poletension-tents">Pole/Tension Tents</a><br/>
                        <span class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78291/frame-tents">Frame Tents</a><br/> <span
                            class="divider"></span></li>
                    <li><a href="http://mygrandrental.com/catalog/78292/globe-lighting">Globe Lighting</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78262/tabletop">Tabletop</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78263/furniture">Furniture</a><br/></li>
            <li><a href="http://mygrandrental.com/catalog/78264/dcor">Décor</a><br/> <span class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78273/archescolumns">Arches/Columns</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78265/heatingcooling">Heating/Cooling</a><br/> <span
                    class="divider"></span>
                <ul>
                    <li><a href="http://mygrandrental.com/catalog/78271/patio-heaters">Patio Heaters</a><br/> <span
                            class="divider"></span></li>
                </ul>
                <div class="clear"></div>
            </li>
            <li><a href="http://mygrandrental.com/catalog/78266/linens">Linens</a><br/></li>
        </ul>
        <div class="clear"></div>
    </li>
    <li><a href="http://mygrandrental.com/catalog/78476/home-business">Home & Business</a> <span class="divider"></span>
        <ul>
            <li><a href="http://mygrandrental.com/catalog/78480/pipe-drape">Pipe & Drape</a><br/></li>
        </ul>
        <div class="clear"></div>
    </li>
</ul>
*/