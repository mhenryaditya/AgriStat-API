<?php

namespace App\Http\Controllers;

use App\Models\CropsProduction;
use Illuminate\Http\Request;
use Rubix\ML\Clusterers\KMeans;
use Rubix\ML\CrossValidation\Metrics\RandIndex;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Pipeline;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\ZScaleStandardizer;

class ClusteringController extends Controller
{
    static function trainClustering()
    {
        // set time limit
        set_time_limit(0);

        $data = CropsProduction::all()->except(['id', 'created_at', 'updated_at']);

        $samples = $data->map(function ($item) {
            return [
                // 'year' => (int) $item->year,
                // 'province' => (string) $item->province,
                // 'vegetable' => (string) $item->vegetable,
                'production' => (float) $item->production,
                // 'planted_area' => (float) $item->planted_area,
                // 'harvested_area' => (float) $item->harvested_area,
                // 'fertilizer_type' => (string) $item->fertilizer_type,
                'fertilizer_amount' => (float) $item->fertilizer_amount,
            ];
        })->toArray();

        $dataset = new Unlabeled($samples);

        $pipeline = new Pipeline([
            new OneHotEncoder(),
            new ZScaleStandardizer(),
        ], new KMeans(3));

        // Train the model
        $pipeline->train($dataset);

        // Calculate rand index score
        // $unlabeled = new Unlabeled($samples);
        // $predictions = $pipeline->predict($unlabeled);
        // $metric = new RandIndex();
        // $ri = $metric->score($predictions, );

        // Save the model
        $modelPath = storage_path('../app/Http/Services/clustering_model.rbx');
        $persistence = new Filesystem($modelPath);
        $model = new PersistentModel($pipeline, $persistence);
        $model->save();
    }
}
