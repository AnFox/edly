<?php

namespace App\Observers;

use App\Contracts\Repositories\ChatMessageRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Events\ChatMessageUpdated;
use App\Events\WebinarUpdated;
use App\Models\Product;

/**
 * Class ProductObserver
 * @package App\Observers
 */
class ProductObserver
{
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;

    /**
     * ProductObserver constructor.
     * @param ProductRepository $productRepository
     * @param WebinarRepository $webinarRepository
     * @param ChatMessageRepository $chatMessageRepository
     */
    public function __construct(ProductRepository $productRepository, WebinarRepository $webinarRepository, ChatMessageRepository $chatMessageRepository)
    {
        $this->productRepository = $productRepository;
        $this->webinarRepository = $webinarRepository;
        $this->chatMessageRepository = $chatMessageRepository;
    }

    /**
     * @param Product $product
     */
    public function updated(Product $product)
    {
        if ($webinar = $this->webinarRepository->getCurrentWebinar($product->banner->room_id)) {
            event(new WebinarUpdated($webinar));

            $this->chatMessageRepository->getBannerMessages($product->banner)->each(function ($message) {
                event(new ChatMessageUpdated($message));
            });
        }
    }
}
