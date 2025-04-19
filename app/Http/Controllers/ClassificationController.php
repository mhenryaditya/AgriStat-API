<?php

namespace App\Http\Controllers;

use App\Models\CropsProduction;
use Illuminate\Http\Request;
use Rubix\ML\Classifiers\AdaBoost;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\LogisticRegression;
use Rubix\ML\Classifiers\NaiveBayes;
use Rubix\ML\Classifiers\OneVsRest;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Classifiers\SoftmaxClassifier;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\Reports\MulticlassBreakdown;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Classifiers\GradientBoost;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\ZScaleStandardizer;

class ClassificationController extends Controller
{
    static function trainFertilizerType()
    {
        // set time limit
        set_time_limit(0);

        $data = CropsProduction::all()->except(['id', 'created_at', 'updated_at']);

        $samples = $data->map(function ($item) {
            return [
                'year' => (int) $item->year,
                'province' => (string) $item->province,
                'vegetable' => (string) $item->vegetable,
                'production' => (float) $item->production,
                'planted_area' => (float) $item->planted_area,
                'harvested_area' => (float) $item->harvested_area,
            ];
        })->toArray();

        $labels = $data->pluck('fertilizer_type')->map(fn($v) => (string) $v)->toArray();

        $dataset = new Labeled($samples, $labels);

        $pipeline = new Pipeline([
            new OneHotEncoder(),
            new ZScaleStandardizer(),
        ], new SoftmaxClassifier());

        // Train the model
        $pipeline->train($dataset);

        // Calculate metrics
        $unlabeled = new Unlabeled($samples);
        $predictions = $pipeline->predict($unlabeled);
        $accuracy = new MulticlassBreakdown();
        $metrics = $accuracy->generate($predictions, $labels);

        // Save the model
        $modelPath = storage_path('../app/Http/Services/fertilizer_type_model.rbx');
        $persistence = new Filesystem($modelPath);
        $model = new PersistentModel($pipeline, $persistence);
        $model->save();

        return [
            'accuracy' => $metrics['overall']['accuracy'],
            'precision' => $metrics['overall']['precision'],
            'recall' => $metrics['overall']['recall'],
            'f1 score' => $metrics['overall']['f1 score'],
        ];
    }
}
