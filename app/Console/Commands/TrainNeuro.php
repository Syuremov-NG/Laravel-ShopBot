<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Rubix\ML\Loggers\Screen;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\ImageResizer;
use Rubix\ML\Transformers\ImageVectorizer;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Layers\Dropout;
use Rubix\ML\NeuralNet\Layers\BatchNorm;
use Rubix\ML\NeuralNet\ActivationFunctions\ELU;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Extractors\CSV;

class TrainNeuro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neuro:train';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Train neuro';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        $logger = new Screen();

        $logger->info('Loading data into memory');

        $samples = $labels = [];

        foreach (glob('train/*.png') as $file) {
            $samples[] = [imagecreatefrompng($file)];
            $test =  basename($file);
            $labels[] = explode(' ', basename($file))[0];
        }

        $dataset = new Labeled($samples, $labels);

        $estimator = new PersistentModel(
            new Pipeline([
                new ImageResizer(100, 100),
                new ImageVectorizer(),
                new ZScaleStandardizer(),
            ], new MultilayerPerceptron([
                new Dense(200),
                new Activation(new ELU()),
                new Dropout(0.5),
                new Dense(200),
                new Activation(new ELU()),
                new Dropout(0.5),
                new Dense(100, 0.0, false),
                new BatchNorm(),
                new Activation(new ELU()),
                new Dense(100),
                new Activation(new ELU()),
                new Dense(50),
                new Activation(new ELU()),
            ], 256, new Adam(0.0005))),
            new Filesystem('cifar101.rbx', true)
        );

        $estimator->setLogger($logger);

        $estimator->train($dataset);

        $extractor = new CSV('progress.csv', true);

        $extractor->export($estimator->steps());

        $logger->info('Progress saved to progress.csv');

        if (strtolower(trim(readline('Save this model? (y|[n]): '))) === 'y') {
            $estimator->save();
        }

        return Command::SUCCESS;
    }
}
