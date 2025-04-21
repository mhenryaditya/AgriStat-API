<?php

namespace App\Http\Controllers;

use App\Models\CropsProduction;
use Illuminate\Http\Request;
use Rubix\ML\CrossValidation\Metrics\MeanAbsoluteError;
use Rubix\ML\CrossValidation\Metrics\MeanSquaredError;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Regressors\ExtraTreeRegressor;
// use Rubix\ML\Regressors\Random;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Pipeline;
use Rubix\ML\Regressors\GradientBoost;
use Rubix\ML\Regressors\RegressionTree;
use Rubix\ML\Transformers\LambdaFunction;
use Rubix\ML\Transformers\NumericStringConverter;
use Rubix\ML\Transformers\OneHotEncoder;
use Rubix\ML\Transformers\ZScaleStandardizer;
use Rubix\ML\CrossValidation\Metrics\RSquared;

class PredictionController extends Controller
{
    public function predict(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer',
            'province' => 'required|string',
            'vegetable' => 'required|string',
            'planted_area' => 'required|numeric',
            'harvested_area' => 'required|numeric',
        ]);
        // Prediksi Produksi
        $production = $this->predictResult('production', [
            (int) $data['year'],
            (string) $data['province'],
            (string) $data['vegetable'],
            (float) $data['planted_area'],
            (float) $data['harvested_area'],
        ]);
        // Prediksi Jenis Pupuk
        $fertilizerType = $this->predictResult('fertilizer_type', [
            (int) $data['year'],
            (string) $data['province'],
            (string) $data['vegetable'],
            (float) $data['planted_area'],
            (float) $data['harvested_area'],
            (float) $production[0],
        ]);
        // Prediksi Jumlah Pupuk
        $fertilizerAmount = $this->predictResult('fertilizer_amount', [
            (int) $data['year'],
            (string) $data['province'],
            (string) $data['vegetable'],
            (float) $data['planted_area'],
            (float) $data['harvested_area'],
            (string) $fertilizerType[0],
            (float) $production[0],
        ]);
        // Clustering karakteristik pertanian dan penggunaan pupuk
        $clustering = $this->predictResult('clustering', [
            // (int) $data['year'],
            // (string) $data['province'],
            // (string) $data['vegetable'],
            (float) $production[0],
            // (float) $data['planted_area'],
            // (float) $data['harvested_area'],
            // (string) $fertilizerType[0],
            (float) $fertilizerAmount[0],
        ]);
        $clusterLabel = match ($clustering[0]) {
            0 => 'Produktifitas rendah namun penggunaan pupuk tinggi',
            1 => 'Produktifitas menengah hingga tinggi dengan penggunaan pupuk yang efisien',
            2 => 'Produktifitas rendah dengan penggunaan pupuk moderate',
            default => 'Unknown Cluster',
        };
        return response()->json([
            'message' => 'Prediction successful',
            'data' => [
                'production' => $production[0],
                'fertilizer_type' => $fertilizerType[0],
                'fertilizer_amount' => $fertilizerAmount[0],
                'clustering' => [
                    'cluster' => $clustering[0],
                    'meaning' => $clusterLabel,
                ],
            ],
        ]);
    }

    public function trainModels()
    {
        // production prediction
        $resultProduction = $this->trainProduksi();
        // fertilizer_type classification
        $resultFertilizerType = ClassificationController::trainFertilizerType();
        // fertilizer_amount prediction
        $resultFertilizer = $this->trainPupuk();
        // clustering
        ClusteringController::trainClustering();
        return response()->json([
            'message' => 'Models trained successfully',
            'data' => [
                'production' => $resultProduction,
                'fertilizer_amount' => $resultFertilizer,
                'fertilizer_type' => $resultFertilizerType,
            ],
        ]);
    }

    public function trainProduksi()
    {
        $data = CropsProduction::all()->except(['id', 'created_at', 'updated_at']);

        $samples = $data->map(function ($item) {
            return [
                'year' => (int) $item->year,
                'province' => (string) $item->province,
                'vegetable' => (string) $item->vegetable,
                'planted_area' => (float) $item->planted_area,
                'harvested_area' => (float) $item->harvested_area,
            ];
        })->toArray();

        $labels = $data->pluck('production')->map(fn($v) => (float) $v)->toArray();

        $dataset = new Labeled($samples, $labels);

        $pipeline = new Pipeline([
            new OneHotEncoder(),
            new ZScaleStandardizer(),
        ], new ExtraTreeRegressor(200, 1));

        // Train the model
        $pipeline->train($dataset);

        // Calculate metrics
        $unlabeled = new Unlabeled($samples);
        $predictions = $pipeline->predict($unlabeled);

        // Evaluate using Mean Absolute Error
        $mae = new MeanAbsoluteError();
        $maeScore = $mae->score($predictions, $labels);

        // Evaluate using Mean Squared Error
        $mse = new MeanSquaredError();
        $mseScore = $mse->score($predictions, $labels);

        // Evaluate using R2 score (coefficient of determination)
        $r2 = new RSquared();
        $r2score = $r2->score($predictions, $labels);

        // Save the model
        $persistence = new Filesystem(storage_path('../app/Http/Services/production_model.rbx'));
        $model = new PersistentModel($pipeline, $persistence);
        $model->save();

        return [
            'mae' => $maeScore,
            'mse' => $mseScore,
            'r2' => $r2score,
        ];
    }

    public function trainPupuk()
    {
        $data = CropsProduction::all()->except(['id', 'created_at', 'updated_at']);

        $samples = $data->map(function ($item) {
            return [
                'year' => (int) $item->year,
                'province' => (string) $item->province,
                'vegetable' => (string) $item->vegetable,
                'planted_area' => (float) $item->planted_area,
                'harvested_area' => (float) $item->harvested_area,
                'fertilizer_type' => (string) $item->fertilizer_type,
                'production' => (float) $item->production,
            ];
        })->toArray();

        $labels = $data->pluck('fertilizer_amount')->map(fn($v) => (float) $v)->toArray();

        $dataset = new Labeled($samples, $labels);

        $pipeline = new Pipeline([
            new OneHotEncoder(),
            new ZScaleStandardizer(),
            new NumericStringConverter(),
        ], new ExtraTreeRegressor(200, 1));

        // Train the model
        $pipeline->train($dataset);

        // Calculate metrics
        $unlabeled = new Unlabeled($samples);
        $predictions = $pipeline->predict($unlabeled);

        // Evaluate using Mean Absolute Error
        $mae = new MeanAbsoluteError();
        $maeScore = $mae->score($predictions, $labels);

        // Evaluate using Mean Squared Error
        $mse = new MeanSquaredError();
        $mseScore = $mse->score($predictions, $labels);

        // Evaluate using R2 score (coefficient of determination)
        $r2 = new RSquared();
        $r2score = $r2->score($predictions, $labels);

        // Save the model
        // $persistence = new Filesystem(storage_path('../app/Http/Services/fertilizer_model.rbx'));
        // $model = new PersistentModel($pipeline, $persistence);
        // $model->save();

        return [
            'mae' => $maeScore,
            'mse' => $mseScore,
            'r2' => $r2score,
        ];
    }

    private function predictResult(string $category, array $data)
    {
        $modelPath = "";
        if ($category == 'production') {
            $modelPath = '../app/Http/Services/production_model.rbx';
        } elseif ($category == 'fertilizer_type') {
            $modelPath = '../app/Http/Services/fertilizer_type_model.rbx';
        } elseif ($category == 'fertilizer_amount') {
            $modelPath = '../app/Http/Services/fertilizer_model.rbx';
        } elseif ($category == 'clustering') {
            $modelPath = '../app/Http/Services/clustering_model.rbx';
        } else {
            return response()->json([
                'message' => 'Invalid category',
            ], 400);
        }
        $persistence = new Filesystem(storage_path($modelPath));
        $model = PersistentModel::load($persistence);
        $predicted = $model->predict(new Unlabeled([$data]));
        return $predicted;
    }
}
