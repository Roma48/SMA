<?php

namespace CTO\AppBundle\Command;

use Carbon\Carbon;
use CTO\AppBundle\Entity\Car;
use CTO\AppBundle\Entity\Model;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ParseandLoadCarsCollectionCommand extends ContainerAwareCommand
{
    const AUTO_SITE_URL = "http://www.autonet.ru/auto/ttx";
    const AUTORIA_SITE_URL = "http://auto.ria.com";

    protected function configure()
    {
        $this
            ->setName('cto:cars:parse')
            ->setDescription('Parse and load car brand collection and models this cars');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $question = "
             1 - autonet.ru
             2 - auto.ria.com
        ";

        $questionForHuman = [
            1 => "autonet.ru",
            2 => "auto.ria.com",
        ];
        $output->write("Select site:");
        $output->writeln($question);

        /** @var QuestionHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        $siteNumber = (int)$dialog->ask($output,
            'Your choice [2]: ',
            2
        );
        $output->write("You entered: ");
        if ($siteNumber > 0 && $siteNumber < 3 ) {
            $output->write($questionForHuman[$siteNumber]."\n");
        } else {
            $output->writeln('Wrong choice. Correct choice from 1 to 2');
            $output->writeln("\nGoodbye.");
            return;
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $brandsCount = 0;
        $modelsCount = 0;

        $startTime = Carbon::now();
        $output->writeln("");
        $output->writeln("Loading ...");

        $client = new Client();
        $result = [];

        if ($siteNumber == 1) {
            $response = $client->get(self::AUTO_SITE_URL);
            $crawler = new Crawler($response->getBody()->getContents(), self::AUTO_SITE_URL);
            $filtered = $crawler->filter('div.brands-block.bt-null ul li a')->each(function (Crawler $node) {

                return ['marka' => $node->text(), "link" => $node->link()->getUri()];
            });

            foreach ($filtered as $item) {
                $response = $client->get($item['link']);
                $crawler = new Crawler($response->getBody()->getContents(), $item['link']);
                $resultModel = $crawler->filter('div.filter-models.bt-null ul li a')->each(function (Crawler $node) {

                    return ['model' => $node->text(), "link" => $node->link()->getUri()];
                });
                $result[] = [
                    "marka" => $item['marka'],
                    "marka_url" => $item['link'],
                    "models" => $resultModel
                ];
            }
        }

        if ($siteNumber == 2) {
//          http://auto.ria.com/api/categories/1/marks/{brandID}/models/_with_count
            $brandIDs = ["98" => "Acura", "2396" => "Adler", "2" => "Aixam", "3" => "Alfa Romeo", "100" => "Alpine", "101" => "Aro", "3105" => "Artega", "4" => "Asia", "5" => "Aston Martin", "6" => "Audi", "7" => "Austin", "103" => "Barkas", "1540" => "Baw", "8" => "Bentley", "106" => "Bertone", "3127" => "Bio Auto", "9" => "BMW", "211" => "BOVA", "329" => "Brilliance", "109" => "Bugatti", "110" => "Buick", "386" => "BYD", "11" => "Cadillac", "407" => "Chana", "1567" => "Changhe", "190" => "Chery", "13" => "Chevrolet", "14" => "Chrysler", "15" => "Citroen", "17" => "Dacia", "198" => "Dadi", "18" => "Daewoo", "115" => "Daf", "1441" => "DAF / VDL", "19" => "Daihatsu", "4206" => "Datsun", "2243" => "DKW", "118" => "Dodge", "308" => "Dongfeng", "120" => "Eagle", "121" => "FAW", "22" => "Ferrari", "23" => "Fiat", "3444" => "Fisker", "24" => "Ford", "25" => "FSO", "197" => "FUQI", "185" => "Geely", "123" => "GMC", "183" => "Gonow", "124" => "Great Wall", "1575" => "Groz", "191" => "Hafei", "1784" => "Hanomag", "28" => "Honda", "2595" => "Huabei", "388" => "Huanghai", "127" => "Hummer", "29" => "Hyundai", "128" => "Infiniti", "3821" => "Iran Khodro", "30" => "Isuzu", "175" => "Iveco", "317" => "JAC", "31" => "Jaguar", "32" => "Jeep", "335" => "Jiangnan", "2231" => "Jinbei", "1574" => "Jonway", "412" => "Karosa", "33" => "Kia", "2676" => "King Long", "35" => "Lamborghini", "36" => "Lancia", "37" => "Land Rover", "406" => "Landwind", "134" => "LDV", "38" => "Lexus", "334" => "Lifan", "135" => "Lincoln", "41" => "Lotus", "45" => "Maserati", "46" => "Maybach", "47" => "Mazda", "3101" => "McLaren", "1904" => "MEGA", "48" => "Mercedes-Benz", "144" => "Mercury", "49" => "MG", "147" => "MINI", "52" => "Mitsubishi", "53" => "Morgan", "55" => "Nissan", "2045" => "Nysa", "148" => "Oldsmobile", "2963" => "Oltcit", "56" => "Opel", "3193" => "Packard", "346" => "Peterbilt", "58" => "Peugeot", "181" => "Plymouth", "149" => "Pontiac", "59" => "Porsche", "60" => "Proton", "1332" => "Quicksilver", "62" => "Renault", "63" => "Rolls-Royce", "64" => "Rover", "65" => "Saab", "3437" => "Saipa", "192" => "Samand", "325" => "Samsung", "331" => "Saturn", "3268" => "Scion", "67" => "Seat", "1726" => "Selena", "195" => "Shuanghuan", "70" => "Skoda", "311" => "SMA", "71" => "Smart", "194" => "SouEast", "411" => "Spyker", "73" => "SsangYong", "75" => "Subaru", "76" => "Suzuki", "77" => "Talbot", "2497" => "Tarpan Honker", "78" => "TATA", "204" => "Tatra", "2233" => "Tesla", "4237" => "Think Global", "1578" => "Tianma", "2050" => "Tiger", "79" => "Toyota", "345" => "Trabant", "80" => "Triumph", "206" => "Van Hool", "82" => "Vauxhall", "84" => "Volkswagen", "85" => "Volvo", "2021" => "Wanderer ", "339" => "Wartburg", "1992" => "Wiesmann", "2653" => "Wuling", "338" => "Xin kai", "87" => "Yugo", "2309" => "Zastava", "315" => "Zhong", "2958" => "Zimmer", "3610" => "Zotye", "3089" => "Zuk", "322" => "ZX", "173" => "АВИА", "188" => "Богдан", "88" => "ВАЗ", "90" => "ВИС ", "91" => "ГАЗ", "410" => "ГолАЗ", "165" => "Другое", "170" => "ЕРАЗ", "169" => "Жук", "89" => "ЗАЗ", "168" => "ЗИЛ", "1544" => "ЗИМ", "186" => "ЗИС", "92" => "ИЖ", "189" => "ЛуАЗ", "94" => "Москвич / АЗЛК", "327" => "РАФ", "199" => "Ретро автомобили", "96" => "СеАЗ", "2491" => "СМЗ", "93" => "УАЗ", "390" => "ХАЗ"];

            $tmp = [];
            foreach ($brandIDs as $key => $value) {
                foreach(json_decode($client->get("http://auto.ria.com/api/categories/1/marks/".(int)$key."/models/_with_count")->getBody()->getContents(), true) as $model) {
                    $tmp[] = ["model" => $model['name']];
                }
                $result[] = [
                    "marka" => $value,
                    "models" => $tmp
                ];
                $tmp = [];
            }
        }

        foreach ($result as $item) {
            $brand = new Car();
            $brand->setName($item['marka']);
            $em->persist($brand);

            foreach ($item['models'] as $modelItem) {
                $model = new Model();
                $model->setName($item['marka'].' - '.$modelItem['model']);

                $em->persist($model);

                $brand->addModel($model);
                $modelsCount++;
            }
            $brandsCount++;
        }

        $em->flush();

        $finishTime = Carbon::now();

        $output->writeln("");
        $output->writeln("Done.");
        $output->writeln(" Car brands loaded: ".$brandsCount);
        $output->writeln(" Car models loaded: ".$modelsCount);
        $output->writeln(" Elapsed time: ".$finishTime->diffForHumans($startTime)." start.");
        $output->writeln("");
    }
}
