<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateCatalog\GenerateCatalogMainJob;
use App\Models\BlogPost;
use Carbon\Carbon;

class DiggingDeeperController extends Controller
{
    /**
     * Базовая информация:
     * @url https://laravel.com/docs/5.8/collections
     *
     * Справочная информация:
     * @url https://laravel.com/api/5.8/Illuminate/Support/Collection.html
     *
     * Вариант коллекции для моделей eloquent:
     * @url https://laravel.com/api/5.8/Illuminate/Database/Eloquent/Collection.html
     *
     * Билдер запросов - то с чем можно перепутать коллекции:
     * @url https://laravel.com/docs/5.8/queries
     */
    public function collections()
    {
        $result = [];

        /**
         * @var \Illuminate\Database\Eloquent\Collection $eloquentCollection
         */
        $eloquentCollection = BlogPost::withTrashed()->get();

//        dd(__METHOD__, $eloquentCollection, $eloquentCollection->toArray());
        /**
         * @var \Iluminate\Support\Collection $collection
         */
        $collection = collect($eloquentCollection->toArray());

//        dd(
//            get_class($eloquentCollection),
//            get_class($collection),
//            $collection
//        );

        $result['first'] = $collection->first();
        $result['last'] = $collection->last();

        $result['where']['data'] = $collection
            ->where('category_id', 10)
            ->values()
            ->keyBy('id');


        $result['where']['count'] = $result['where']['data']->count();
        $result['where']['isEmpty'] = $result['where']['data']->isEmpty();
        $result['where']['isNotEmpty'] = $result['where']['data']->isNotEmpty();
//        dd($result);

//        // Не очень красиво
//        if ($result['where']['count']) {
//            //....
//        }
//        // Так лучше
//        if ($result['where']['data']->isNotEmpty) {
//            //....
//        }
//
//        $result['where_first'] = $collection->firstWhere('created_at', '>', '2019-01-17 01:35:11');

        //Базовая переменная не изменится. Просто вернутся измененная версия.
        $result['map']['all'] = $collection->map(function (array $item) {
            $newItem = new \stdClass();
            $newItem->item_id = $item['id'];
            $newItem->item_name = $item['title'];
            $newItem->exists = is_null($item['deleted_at']);
            return $newItem;
        });

//        $result['map']['not_exist'] = $result['map']['all']->where('exists', '=', false)->values()->keyBy('item_id');
        //
        $collection->transform(function (array $item) {
            $newItem = new \stdClass();
            $newItem->item_id = $item['id'];
            $newItem->item_name = $item['title'];
            $newItem->exists = is_null($item['deleted_at']);
            $newItem->created_at = Carbon::parse($item['created_at']);

            return $newItem;
        });

        $newItem = new \stdClass();
        $newItem->id = 9999;

        $newItem2 = new \stdClass();
        $newItem2->id = 8888;

//        //Установить элемент в начало коллекции
//        $newItemFirst = $collection->prepend($newItem)->first();
//        $newItemLast = $collection->push($newItem2)->last();
//        $pulledItem = $collection->pull(10);

//        dd(compact('collection', 'newItemFirst', 'newItemLast', 'pulledItem'));

        // Фильтрация. Замена orWhere()
//        $filtered = $collection->filter(function ($item) {
//            $byDay = $item->created_at->isFriday();
//            $byDate = $item->created_at->day == 3;
//
//            //$result = $item->created_at->isFriday() && ($item->created_at->day == 11);
//            $result = $byDay && $byDate;
//            return $result;
//        });
//
//        dd(compact('filtered'));

        $sortedSimpleCollection = collect([5, 3, 1, 2 , 4])->sort();
        $sortedAscCollection = $collection->sortBy('created_at');
        $sortedDescCollection = $collection->sortByDesc('item_id');

        dd(compact('sortedSimpleCollection', 'sortedAscCollection', 'sortedDescCollection'));
    }

    public function prepareCatalog()
    {
        GenerateCatalogMainJob::dispatch();
    }
}
