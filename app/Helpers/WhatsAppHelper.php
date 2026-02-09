<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Request;

class WhatsAppHelper
{
    /**
     * Generate WhatsApp message URL for a product
     *
     * @param object $product Product model with name, price, sku, short_description
     * @param int $quantity Quantity selected
     * @return string WhatsApp URL
     */
    public static function generateProductLink($product, $quantity = 1)
    {
        $phoneNumber = config('services.whatsapp.number');
        
        if (!$phoneNumber) {
            return '';
        }

        $message = self::buildProductMessage($product, $quantity);
        $encodedMessage = urlencode($message);
        
        return "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
    }

    /**
     * Build WhatsApp message with product details
     *
     * @param object $product
     * @param int $quantity
     * @return string
     */
    private static function buildProductMessage($product, $quantity = 1)
    {
        $productName = $product->name ?? 'Product';
        $price = isset($product->price) ? number_format($product->price, 2, ',', '.') : '0,00';
        $sku = $product->sku ?? '';
        $description = $product->short_description ?? '';
        
        // Get current page URL
        $productUrl = route('products.show', $product);
        
        // Build message with proper formatting
        $message = "*" . __('product.whatsapp_greeting') . "*\n\n";
        $message .= "*" . __('product.whatsapp_product') . ":* " . $productName . "\n";
        $message .= "*" . __('product.whatsapp_price') . ":* €" . $price . "\n";
        $message .= "*" . __('product.whatsapp_quantity') . ":* " . $quantity . "\n";
        
        if ($sku) {
            $message .= "*" . __('product.whatsapp_sku') . ":* " . $sku . "\n";
        }
        
        if ($description) {
            $message .= "\n*" . __('product.whatsapp_details') . ":*\n" . $description . "\n";
        }
        
        // Add product link on its own line for WhatsApp to detect as clickable
        $message .= "\n" . __('product.whatsapp_view_product') . "\n" . $productUrl;
        
        return $message;
    }

    /**
     * Get WhatsApp phone number from config
     *
     * @return string|null
     */
    public static function getPhoneNumber()
    {
        return config('services.whatsapp.number');
    }

    /**
     * Check if WhatsApp integration is enabled
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return !empty(self::getPhoneNumber());
    }
}
