<?php

include __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Loggers\Screen;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;

ini_set('memory_limit', '-1');

$logger = new Screen();

$logger->info('Loading data into memory');

$samples = $labels = [];

foreach (glob('valid/*.jpg') as $file) {
    $samples[] = [imagecreatefromjpeg($file)];
    $labels[] = preg_replace('/[0-9]+_(.*).jpg/', '$1', basename($file));
}

$dataset = new Labeled($samples, $labels);

$estimator = PersistentModel::load(new Filesystem('cifar10.rbx'));

$logger->info('Making predictions');

$predictions = $estimator->proba($dataset);

arsort($predictions[0]); // сортирует массив в порядке убывания по значениям
$predictions = array_slice($predictions[0], 0, 5); // выбирает первые n элементов

print_r($predictions); // выводит массив с n максимальными значениями

//$predictions = $estimator->predict($dataset);
//
//$report = new AggregateReport([
//    new MulticlassBreakdown(),
//    new ConfusionMatrix(),
//]);
//
//$results = $report->generate($predictions, $dataset->labels());
//
//echo $results;
//
//$results->toJSON()->saveTo(new Filesystem('report.json'));

$logger->info('Report saved to report.json');
