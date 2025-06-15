<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrganizationRepository;
use App\Repositories\ProductRepository;
use App\Values\Collections\ProductCollection;
use App\Values\Product;

class ProductService
{
    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected ProductRepository $productRepository
    ) {
    }

    public function all(bool $onlyActive = true): ProductCollection
    {
        return Product::collect($this->productRepository->all($onlyActive));
    }

    public function find(string $id): Product
    {
        $product = $this->productRepository->find($id);

        return Product::from($product);
    }
}
