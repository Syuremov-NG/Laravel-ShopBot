<?php

namespace App\Neuro;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Psr\Log\LoggerInterface;
use Rubix\ML\Loggers\Screen;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\CrossValidation\Reports\AggregateReport;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Illuminate\Support\Facades\Log;

class ValidImage
{
    public function validate(string $fileName)
    {
        ini_set('memory_limit', '-1');

        $logger = new Screen();

        $logger->info('Loading data into memory');

        $path = public_path("valid/$fileName.jpg");
        $samples = [[imagecreatefromjpeg($path)]];
        $labels = [$fileName];

        $dataset = new Labeled($samples, $labels);

        $estimator = PersistentModel::load(new Filesystem(public_path('cifar10.rbx')));

        $logger->info('Making predictions');

        $predictions = $estimator->proba($dataset);

        File::delete('public/' . "valid/$fileName.jpg");

        arsort($predictions[0]); // сортирует массив в порядке убывания по значениям

        return array_slice($predictions[0], 0, 5);
    }
}
