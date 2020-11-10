<?php
/**
{LICENSE_PLACEHOLDER}
*/

 /**
  * Class ProductController
  */
class ProductController extends ProductControllerCore
{
    /**
     * @TODO legacy block, prestashop could not remove those constants on uninstall
     */
    const IMAGE_ID = 1000000000;
    const IMAGE_LEGEND = 'cappasity-preview';

    /**
     *
     */
    public function initContent()
    {
        parent::initContent();

        $productId = Tools::getValue('id_product', null);

        if ($productId === null) {
            return;
        }

        $cappasityImages = $this->getCappasityImages($productId);

        if (count($cappasityImages) === 0) {
            return;
        }

        $product = $this->context->smarty->getTemplateVars('product');

        if (is_array($product) || $product instanceof ArrayAccess) {
            $this->init17($cappasityImages);
        } else {
            $this->init16($cappasityImages);
        }
    }

    /**
     *
     */
    protected function getCappasityImages($productId)
    {
        $cacheKey = Cappasity3d::CACHE_KEY . $productId;
        $dbManager = new CappasityManagerDatabase(Db::getInstance(), _DB_PREFIX_, _MYSQL_ENGINE_);

        // @todo Does a cache work?
        if (Cache::isStored($cacheKey)) {
            $cappasityImages = Cache::retrieve($cacheKey);
        } else {
            $cappasityImages = $dbManager->getCappasity(array('productId' => (int)$productId));

            if (count($cappasityImages) !== 0) {
                Cache::store($cacheKey, $cappasityImages);
            }
        }

        return $cappasityImages;
    }

    /**
     *
     */
    protected function groupByCappasityImage(array $images = array())
    {
        $cappasityImages = array();

        foreach ($images as $image) {
            $cappasityId = (string)$image['cappasity_id'];
            $variantId = (string)$image['variant_id'];

            if (array_key_exists($cappasityId, $cappasityImages) === false) {
                $imageId = (string)$this->getMockedImageId($image['id']);
                $cappasityImages[$cappasityId] = array(
                  'cappasityId' => $cappasityId,
                  'imageId' => $imageId,
                  'variants' => array((string)$variantId),
                );
            } else {
                $cappasityImages[$cappasityId]['variants'][] = $variantId;
            }
        }

        return $cappasityImages;
    }

    /**
     *
     */
    protected function groupByVariant(array $images = array())
    {
        $cappasityImages = array();

        foreach ($images as $image) {
            $cappasityId = (string)$image['cappasity_id'];
            $variantId = (string)$image['variant_id'];
            $imageId = (string)$this->getMockedImageId($image['id']);

            if (array_key_exists($variantId, $cappasityImages) === false) {
                $cappasityImages[$variantId] = array();
            }

            if (array_key_exists($cappasityId, $cappasityImages[$variantId]) === true) {
                continue;
            }

            $cappasityImages[$variantId][$cappasityId] = array(
              'cappasityId' => $cappasityId,
              'imageId' => $imageId,
              'variantId' => $variantId,
            );
        }

        return $cappasityImages;
    }

    /**
     *
     */
    protected function init17(array $cappasityImages = array())
    {
        $product = $this->context->smarty->getTemplateVars('product');
        $combinationImages = is_array($this->context->smarty->getTemplateVars('combinationImages'))
          ? $this->context->smarty->getTemplateVars('combinationImages')
          : array();
        $productAttributeId = (string)$product['id_product_attribute'];
        $images = $product['images'];
        $hadImages = count($images) > 0;
        $productVariants = $product['main_variants'];

        $groupedByCappasityImage = $this->groupByCappasityImage($cappasityImages);
        $groupedByVariant = $this->groupByVariant($cappasityImages);

        foreach ($productVariants as $productVariant) {
            $productVariantId = (string)$productVariant['id_product_attribute'];
            $cappasityVariantImages = false;

            if (array_key_exists($productVariantId, $groupedByVariant) === true) {
                $cappasityVariantImages = $groupedByVariant[$productVariantId];
            } elseif (array_key_exists('0', $groupedByVariant) === true) {
                $cappasityVariantImages = $groupedByVariant['0'];
            }

            if ($cappasityVariantImages === false) {
                continue;
            }

            if (array_key_exists($productVariantId, $combinationImages) === false) {
                $combinationImages[$productVariantId] = array();
            }

            foreach ($cappasityVariantImages as $cappasityVariantImage) {
                $imageId = (string)$cappasityVariantImage['imageId'];

                array_unshift($combinationImages[$productVariantId], array(
                    'id_product_attribute' => $productVariantId,
                    'id_image' => $imageId,
                ));
            }
        }

        // has variants
        if (array_key_exists($productAttributeId, $groupedByVariant) === true) {
            foreach ($groupedByVariant[$productAttributeId] as $variantImage) {
                array_unshift($images, $this->get17Image($variantImage, $groupedByCappasityImage));
            }
        // has no variants, try to add common image
        } elseif (array_key_exists('0', $groupedByVariant) === true) {
            foreach ($groupedByVariant['0'] as $variantImage) {
                array_unshift($images, $this->get17Image($variantImage, $groupedByCappasityImage));
            }
        }

        $product['images'] = $images;

        if ($hadImages === false) {
            // for 1.7.1.x
            $product['cover'] = $images[0];
            // for 1.7.7.x
            $product['default_image'] = $images[0];
        }

        $this->context->smarty->assign(array(
            'combinationImages' => $combinationImages,
            'product' => $product,
        ));
    }

    /**
     *
     */
    protected function init16(array $cappasityImages = array())
    {
        $groupedByCappasityImage = $this->groupByCappasityImage($cappasityImages);
        // could be null or array
        $templateImages = $this->context->smarty->getTemplateVars('images');
        $images = is_array($templateImages) ? $templateImages : array();
        // could be null or array
        $combinationImages = $this->context->smarty->getTemplateVars('combinationImages');

        foreach ($groupedByCappasityImage as $cappasityImage) {
            $imageId = (string)$cappasityImage['imageId'];
            $variantsIds = $cappasityImage['variants'];
            // add on top of all pictures
            $images = array($imageId => array(
                'cover' => '0',
                'id_image' => $imageId,
                'position' => $imageId,
            )) + $images;

            // add variants
            foreach ($variantsIds as $variantId) {
                if ($variantId === '0') {
                    continue;
                }

                if (is_array($combinationImages) === false) {
                    $combinationImages = array();
                }

                if (array_key_exists($variantId, $combinationImages) === false) {
                    $combinationImages[$variantId] = array();
                }

                // add picture for variant
                array_unshift($combinationImages[$variantId], array(
                     'id_product_attribute' => $variantId,
                     'id_image' => $imageId,
                ));
            }
        }

        $this->context->smarty->assign(array(
            'combinationImages' => $combinationImages,
            'images' => $images,
        ));
    }

    /**
     *
     */
    protected function getImageStub()
    {
        return array(
            'url' => '/modules/cappasity3d/views/img/logo-3d.jpg',
            'width' => 98,
            'height' => 98,
        );
    }

    /**
     *
     */
    protected function getImage($fileId, $width, $height)
    {
        // TODO: make sure we use <module>::SETTING_ALIAS from module const
        $alias = Configuration::get('cappasityAccountAlias');

        return array(
            'url' => "https://api.cappasity.com/api/files/preview/{$alias}/w{$width}-h{$height}-cpad/{$fileId}.jpeg",
            'width' => $width,
            'height' => $height,
        );
    }

    /**
     *
     */
    protected function get17Image($image, $groupedByCappasityImage)
    {
        $imageId = (string)$image['imageId'];
        $cappasityId = (string)$image['cappasityId'];

        return array(
            'bySize' => array(
                ImageType::getFormattedName('small') => $this->getImage($cappasityId, 90, 90),
                ImageType::getFormattedName('cart') => $this->getImage($cappasityId, 125, 125),
                ImageType::getFormattedName('home') => $this->getImageStub(),
                ImageType::getFormattedName('medium') => $this->getImage($cappasityId, 452, 452),
                ImageType::getFormattedName('large') => $this->getImage($cappasityId, 800, 800),
            ),
            'small' => $this->getImageStub(),
            'medium' => $this->getImage($cappasityId, 452, 452),
            'large' => $this->getImage($cappasityId, 800, 800),
            'cover' => '0',
            'id_image' => $imageId,
            'position' => $imageId,
            'associatedVariants' => array_values(array_diff(
                $groupedByCappasityImage[$cappasityId]['variants'],
                array('0')
            )),
        );
    }

    private function getMockedImageId($originalId)
    {
        $initialFakeId = 100000000;

        return $initialFakeId + $originalId;
    }
}
