<?php

namespace App\Jobs\GenerateCatalog;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateCatalogMainJob extends AbstractJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->debug('start');

        // Сначала кешируем продукты
        GenerateCatalogCacheJob::dispatchNow();

        // Затем создаем цепочку заданий формирования файлов с ценами
        $chainPrices = $this->getChainPrices();

        // Основные подзадачи
        $chainMain = [
            new GenerateCategoriesJob, // Генерация категорий
            new GenerateDeliveriesJob, // Генерация способов доставок
            new GeneratePointsJob, // Генерация пунктов выдачи
        ];

        // Подзадачи которые должны выполниться самыми последними
        $chainLast = [
            // Архивирование файлов и перенос архива в публичную папку
            new ArchiveUploadsJob,
            // Отправка уведомления сторонниму сервису о том что можно скачать новый файл каталога
            new SendPriceRequestJob,
        ];

        $chain = array_merge($chainPrices, $chainMain, $chainLast);

        GenerateGoodsFileJob::withChain($chain)->dispatch();

        $this->debug('finish');
    }

    private function getChainPrices()
    {
        $result = [];
        $products = collect([1, 2, 3, 4, 5]);
        $fileNum = 1;

        foreach ($products->chunk(1) as $chunk) {
            $result[] = new GeneratePricesFileChunkJob($chunk, $fileNum);
            $fileNum++;
        }

        return $result;
    }
}
