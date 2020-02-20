<?php

if ( strpos(__DIR__, 'ezad/lib') === false ) {
    require_once 'vendor/autoload.php';
} else {
    require_once '../../autoload.php';
}

$spec = [
    'version' => 1,
    'duration' => 20.0,
    'width' => 1280,
    'height' => 720,
    'items' => [
        [
            'type' => 'background',
            'source' => '',
        ],
        [
            'type' => 'overlay',
            'source' => '',
            'rect' => [0, 620, 1280, 100],
            'angle' => 0,
            'layerIndex' => 3,
            'timeRange' => [0, 20.0],
        ],
        [
            'type' => 'video',
            'source' => '',
            'startPos' => 2.5,
            'timeRange' => [5.0, 15.0],
            'rect' => [0, 0, 640, 360],
            'angle' => 0,
            'layerIndex' => 1,
        ],
        [
            'type' => 'video',
            'source' => '',
            'startPos' => 7.5,
            'timeRange' => [10.0, 20.0],
            'rect' => [640, 360, 640, 360],
            'angle' => 0,
            'layerIndex' => 2,
        ],
    ],
];

use EzAd\Studio\Canvas;
use EzAd\Studio\VideoItem;
use EzAd\Studio\ImageItem;
use EzAd\Studio\Filter as F;

// ffmpeg -f lavfi -i "color=c=red:d=10:size=hd720" red10.m4v
// should we change to hd1080 later, may need to add a probesize 6220801 option
//
// $color = new ColorSrc();
// $color->color = 'red';
// $color->duration = 10;
// $color->size = VideoConstants::SIZE_HD720;
// $cmd = 'ffmpeg -f lavfi -i "' . $color->writeItem() . '" red10.m4v';

$canvas = new Canvas(1280, 720);
$canvas->setBackgroundImage('fantasyspace.jpg');

$video1 = new VideoItem();
$video1->setSource('hardsubs.m4v')
    ->setRect(0, 0, 640, 360)
    ->setLayerIndex(1)
    ->setTimeRange(0, 10)
    ->setEndBehavior('pass');

$video2 = new VideoItem();
$video2->setSource('woo.m4v')
    ->setRect(640, 360, 640, 360)
    ->setLayerIndex(2)
    ->setTimeRange(3, 13)
    ->setSeekTo(4)
    ->setEndBehavior('repeat');

$image = new ImageItem();
$image->setSource('fantasyspace.jpg')
    ->setRect(0, 360, 640, 360)
    ->setLayerIndex(3)
    ->setTimeRange(2, 8);

$canvas->addItem($video1);
$canvas->addItem($video2);
$canvas->addItem($image);

$graph = $canvas->generateFilterGraph();
$ser = serialize($graph);
echo $ser, "\n", strlen($ser), "\n";
$ser2 = base64_encode(gzdeflate($ser));
echo $ser2, "\n", strlen($ser2), "\n";

echo 'ffmpeg -f lavfi -i ', $graph->toString(), " out.m4v\n";



// NOTES:
// For making overlays, make sure to have a Setpts(expr=PTS+x/TB) where x = the the time the overlay starts.
// So if the overlay runs from 3-13 seconds, use Setpts(expr=PTS+3/TB)

/*
$x = 3;
$ovchain = new FilterChain('', 'out');
$ovchain->add(MovieSrc::create('woo.m4v'));
$ovchain->add(Setpts::create('PTS+'.$x.'/TB'));
$ovchain->add(Scale::create(640, 360));

$overlay = new Overlay('in1,in2', 'merged');
$overlay->setEnableRange($x, 13)->setXY(100, 100);
*/

// using background images:
/*
ffmpeg -f lavfi -i "
    color=size=1280x720:duration=15:color=0xFFFFFF[bg1];
    movie=filename=fantasyspace.jpg, scale=w=1280:h=720, setpts=expr=PTS-STARTPTS[bg2];
    [bg1][bg2]overlay[bg];

    movie=filename=hardsubs.m4v, setpts=expr=PTS-STARTPTS[ov];
    movie=filename=woo.m4v, setpts=expr=PTS-1/TB, scale=w=640:h=360[ov2];
    [bg][ov]overlay=enable=between(t\\,0\\,10):eof_action=pass[merge1];

    [merge1][ov2]overlay=enable=between(t\\,3\\,13):x=640:y=360
"
out.m4v
*/

// do this someday if EzAd\Studio ever gets refactored out into an open source library
/*$graph->parse("
    ColorSrc(size=hd720, duration=15, color=0xffffff) #bg;
    MovieSrc(filename=hardsubs.m4v), Setpts(expr='PTS-STARTPTS') #ov;
    MovieSrc(filename=woo.m4v), Setpts(expr='PTS+3/TB'), Scale(w=640,h=360) #ov2;
    #bg #ov Overlay(enable='0-10',eofAction=pass) #merge1;
    #merge1 #ov2 Overlay(enable='3-13',x=640,y=360);
");

<graph>
    <items>
        <colorSource id="color" size="hd720" duration="15" color="0xffffff" />
        <chain id="chain1">
            <movieSource filename="hardsubs.m4v" />
            <setpts expr="PTS+3/TB" />
            <scale size="640x360" />
        </chain>
        <overlay enableRange="0-10" in1="color" in2="chain1" />
    </items>
</graph>

{
    "items": [
        {
            "name": "colorSource",
            "id": "color",
            "size": "hd720",
            "duration": 15,
            "color": "FFFFFF"
        },
        {
            "name": "chain",
            "id": "chain1",
            "items": [
                {
                    "name": "movieSource",
                    "filename": "hardsubs.m4v"
                },
                {
                    "name": "setpts",
                    "expr": "PTS+3/TB"
                },
                {
                    "name": "scale",
                    "size": "640x360"
                }
            ]
        },
        {
            "name": "overlay",
            "enableRange": [0, 10],
            "inputs": ["color", "chain1"]
        }
    ]
}

*/

/*$filter = new FilterSet();
list($main, $tmp) = $filter->add(new SplitFilter());
$tmp->add(new CropFilter('iw', 'ih/2', 0, 0));
$tmp->add(new VflipFilter());

$filter->add();


$filterSet->add(new SplitFilter('main', 'tmp'));
$chain = new FilterChain('tmp', 'flip');
$chain->add(new CropFilter('iw', 'ih/2', 0, 0));
$chain->add(new VflipFilter());
$filterSet->add($chain);

$filterSet->add(new OverlayFilter('main', 'flip'));
*/

// test.ass was generated with:
// php ideas.php > test.ass

// hardsubs.m4v was generated with:
// ffmpeg -i 54bd230e0ec57_WINBAG.m4v -to 20 -vf "ass=test.ass,scale=854:480" hardsubs.m4v
// (input file was in ads/2015/01/19)

// actually, just not going to include those files... it's test data, should not be in git

/*use EzAd\Media\Subtitle as S;
$aw = new S\AssWriter();

$entry = new S\Entry(0, 15, 10, (720 - 74) / 2, 'hello world this is longer');
$entry->bold = true;
$entry->strokeWidth = 1;
$entry->strokeColor = 0xff0000;
$entry->fillColor = 0xffff00;
$entry->fontSize = 64;
$entry->rotation = 30;

$entry2 = new S\Entry(3, 13, 300, 400, "hello world\nline num 2");
$aw->writeEntry($entry);
$aw->writeEntry($entry2);

echo $aw->getData(), "\n";*/


/*$addr = new \EzAd\Address\Address();
$addr->setName('test business')
    ->setStreetLines(['123 Test Street'])
    ->setCity('Test City')
    ->setAdminArea('MI')
    ->setPostalCode('12345');

$invoice = new \EzAd\Commerce\Invoicing\Invoice(83755624, 'test business', date_create('now'), 10, $addr);
$invoice->setStoreNumber('000000');
$invoice->setPoNumber('778899');

$invoice->addLineItem(new \EzAd\Commerce\Invoicing\LineItem('mx2', 'EZ-AD TV MX2 Dual Core 8gb Media Player', '149.99'));
$invoice->addLineItem(new \EzAd\Commerce\Invoicing\LineItem('ss', 'Freight Cost', '12.72'));

$trans = new \EzAd\Commerce\Invoicing\Orgill\OrgillEDITransformer();
echo $trans->transform([$invoice]);
 */

/*
demo_properties('US');
demo_properties('CA');
demo_properties('GB');
demo_properties('CN');

demo_country_options();

demo_validate();


function demo_properties($code)
{
    $countries = \EzAd\Address\CountryData::instance();
    $c = $countries[$code];

    echo 'Name: ', $c->getName(), "\n";
    echo 'Postal Type: ', $c->getPostalType(), "\n";
    echo 'State Type: ', $c->getStateType(), "\n";

    if ( $c->hasStates() ) {
        echo "-- STATES --\n";
        foreach ($c->getStates() as $stateCode => $name) {
            echo "[$stateCode] $name\n";
        }
    }
}

function demo_country_options()
{
    $countries = \EzAd\Address\CountryData::instance();
    echo "<select name=\"country\">\n";
    echo "<option value=\"\">Select your country</option>\n";
    foreach ( $countries->options() as $code => $name ) {
        echo "<option value=\"$code\">$name</option>\n";
    }
    echo "</select>\n";
}

function demo_validate()
{
    $us = \EzAd\Address\CountryData::instance()['US'];

    $addr = new \EzAd\Address\Address();
    $addr
        ->setName('Steven Harris')
        ->setOrganization('EZ-AD TV')
        ->setStreetLines(['333 Test Street', 'Apt. 201'])
        ->setAdminArea('MI')
        ->setCity('Clinton Township')
        ->setPostalCode('48035');

    echo $us->formatAddress($addr), "\n";
    echo 'Valid: ', $us->validateAddress($addr) ? 'Yes' : 'No', "\n";
}
*/

/*
$factory = new \EzAd\Ad\Storage\GoogleClientFactory(
    '521143735270-lajs8uibjl8f8h72p16t96ige6l47q51.apps.googleusercontent.com',
    //'521143735270-lajs8uibjl8f8h72p16t96ige6l47q51@developer.gserviceaccount.com',
    'EZ-AD Testing',
    '521143735270-lajs8uibjl8f8h72p16t96ige6l47q51@developer.gserviceaccount.com',
    __DIR__ . '/tmpstoragekey.p12',
    'notasecret',
    ['https://www.googleapis.com/auth/devstorage.read_write']);

$client = $factory->create();

$uploader = new \EzAd\Util\GCSUploader($client, 1048576 * 2);
$uploader->retryingUpload('algs4.pdf', 'crindigo-bucket-1', 'books/', function($nc, $total, $att) {
    echo "$att# $nc/$total\n";
});
*/

/*
$bf = new \EzAd\Util\BloomFilter(4096, 128);
$bf->put("hello");
$bf->put("world");
for ( $i = 1; $i < 50; $i++ ) {
    //$bf->put("msg $i");
}

$ser = serialize($bf);
echo $ser;

$bf2 = unserialize($ser);
var_dump($bf2->maybeExists("world"));
var_dump($bf2->maybeExists("nope"));
*/

/*
$clientId = '';
$accountName = '';
$keyFile = __DIR__ . '/tmpstoragekey.p12';
$keyPassword = 'notasecret';
$chunkSize = 8 * 1024 * 1024;

$client = new Google_Client();
$client->setApplicationName('EZ-AD Testing');
$client->setClientId($clientId);
$service = new Google_Service_Storage($client);

$key = file_get_contents($keyFile);
$cred = new Google_Auth_AssertionCredentials($accountName,
    ['https://www.googleapis.com/auth/devstorage.read_write'], $key, $keyPassword);

$client->setAssertionCredentials($cred);
if ( $client->getAuth()->isAccessTokenExpired() ) {
    $client->getAuth()->refreshTokenWithAssertion($cred);
}
echo "Access Token: ", $client->getAccessToken(), "\n";

$object = new Google_Service_Storage_StorageObject();
$object->setName('compilers.pdf');

$client->setDefer(true);
$request = $service->objects->insert('crindigo-bucket-1', $object, []);

$media = new Google_Http_MediaFileUpload($client, $request, 'application/pdf', null, true, $chunkSize);
$media->setFileSize(filesize('compilers.pdf'));

// Upload the various chunks. $status will be false until the process is
// complete.
$status = false;
$handle = fopen('compilers.pdf', "rb");
while ( !$status && !feof($handle) ) {
    $chunk = fread($handle, $chunkSize);
    $status = $media->nextChunk($chunk);
}

// The final value of $status will be the data from the API for the object
// that has been uploaded.
$result = false;
if ( $status != false ) {
    $result = $status;
}

fclose($handle);
*/
