<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Services\Concerns;

trait MagentoSelectorMaps
{
    private function getSelectorMapping(string $platform): array
    {
        return match ($platform) {
            'magento-luma' => $this->getLumaSelectors(),
            'magento-hyva' => $this->getHyvaSelectors(),
            default        => $this->getGenericSelectors(),
        };
    }

    private function getLumaSelectors(): array
    {
        return [
            'homepage' => [
                'nav_menu'          => '.navigation',
                'search_input'      => '#search',
                'cart_icon'         => 'a.action.showcart',
                'cart_count'        => 'span.counter.qty',
                'hero_banner'       => '.pagebuilder-banner-wrapper',
                'featured_products' => '.products-grid',
            ],
            'category' => [
                'category_title'  => '.page-title span',
                'product_item'    => '.product-item',
                'product_link'    => '.product-item-link',
                'sort_dropdown'   => '.sorter-options',
                'filter_toggle'   => '.filter-options-title',
                'filter_option'   => '.filter-options-item',
                'pagination_next' => 'a.action.next',
            ],
            'product' => [
                'product_title'   => '.product-info-main .page-title span',
                'product_price'   => '.product-info-price .price',
                'product_gallery' => '.fotorama__stage',
                'qty_input'       => '#qty',
                'product_sku'     => '.product.attribute.sku .value',
                'product_desc'    => '.product.attribute.description .value',
            ],
            'add_to_cart' => [
                'add_to_cart_button' => 'button#product-addtocart-button',
                'cart_count'         => 'span.counter.qty',
                'success_message'    => '.message-success',
            ],
            'cart' => [
                'cart_container'  => '.cart-container',
                'cart_item'       => '.cart.items tbody tr',
                'qty_input'       => '.input-text.qty',
                'update_cart'     => 'button[data-cart-item-update]',
                'remove_item'     => 'a.action.delete',
                'cart_subtotal'   => '.sub.totals .price',
                'checkout_button' => 'button.action.primary.checkout',
            ],
            'guest_checkout' => [
                'guest_option'       => '#login-form [value="guest"]',
                'email_input'        => '#customer-email',
                'firstname'          => '#billing-new-address-form input[name="firstname"]',
                'lastname'           => '#billing-new-address-form input[name="lastname"]',
                'street'             => '#billing-new-address-form input[name="street[0]"]',
                'city'               => '#billing-new-address-form input[name="city"]',
                'postcode'           => '#billing-new-address-form input[name="postcode"]',
                'phone'              => '#billing-new-address-form input[name="telephone"]',
                'shipping_method'    => 'input[name="ko_unique_1"]',
                'payment_method'     => '#checkmo',
                'place_order_button' => 'button.action.primary.checkout',
                'success_heading'    => '.checkout-success h1',
            ],
            'account_registration' => [
                'register_form'       => 'form.form-create-account',
                'firstname'           => '#firstname',
                'lastname'            => '#lastname',
                'email'               => '#email_address',
                'password'            => '#password',
                'password_confirm'    => '#password-confirmation',
                'submit_button'       => 'button.action.submit.primary',
                'success_message'     => '.message-success',
                'account_nav'         => '.block-collapsible-nav',
            ],
            'auth' => [
                'login_form'     => '#login-form',
                'email_input'    => '#email',
                'password_input' => '#pass',
                'sign_in_button' => '#send2',
                'account_link'   => '.customer-name',
                'logout_link'    => 'a[href*="customer/account/logout"]',
                'login_error'    => '.message-error',
            ],
            'search' => [
                'search_input'   => '#search',
                'search_button'  => 'button[title="Search"]',
                'results_title'  => '.search.results .block-subtitle',
                'product_item'   => '.search.results .product-item',
                'product_link'   => '.search.results .product-item-link',
                'no_results'     => '.search.results .message.notice',
            ],
        ];
    }

    private function getHyvaSelectors(): array
    {
        return [
            'homepage' => [
                'nav_menu'          => 'header nav',
                'search_input'      => '[x-data*="search"] input[type="text"]',
                'cart_icon'         => '[x-data*="cart"] button',
                'cart_count'        => '[x-data*="cart"] [x-text]',
                'hero_banner'       => '.hero, [data-content-type="banner"]',
                'featured_products' => '[x-data*="product"], .products-grid',
            ],
            'category' => [
                'category_title'  => 'h1.page-title, main h1',
                'product_item'    => '.product-item-info',
                'product_link'    => '.product-item-info a.product-item-photo',
                'sort_dropdown'   => '[x-data*="sort"] select, select.sorter-option',
                'filter_toggle'   => '[x-data*="filter"] button, .filter-options-title',
                'filter_option'   => '[x-data*="filter"] a, .filter-options-content a',
                'pagination_next' => 'a[aria-label="Next"]',
            ],
            'product' => [
                'product_title'   => 'h1.page-title, [x-data*="product"] h1',
                'product_price'   => '[x-data*="price"] .price, .product-info-price .price',
                'product_gallery' => '[x-data*="gallery"], .product-media-container',
                'qty_input'       => 'input[name="qty"]',
                'product_sku'     => '[itemprop="sku"]',
                'product_desc'    => '[x-data*="description"] .value, .product-info-main .description',
            ],
            'add_to_cart' => [
                'add_to_cart_button' => 'form[action*="checkout/cart/add"] button[type="submit"]',
                'cart_count'         => '[x-data*="cart"] [x-text]',
                'success_message'    => '[x-data*="messages"] .message-success, .message-success',
            ],
            'cart' => [
                'cart_container'  => '[x-data*="cart"], .cart-container',
                'cart_item'       => '[x-data*="cartItem"], .cart.items tbody tr',
                'qty_input'       => 'input[name="cart[*][qty]"], .input-text.qty',
                'update_cart'     => 'button[x-on*="update"], button[data-cart-item-update]',
                'remove_item'     => '[x-on*="remove"], a.action.delete',
                'cart_subtotal'   => '[x-data*="cart"] .price, .sub.totals .price',
                'checkout_button' => 'a[href*="checkout"][class*="primary"], button.btn-proceed-checkout',
            ],
            'guest_checkout' => [
                'guest_option'       => 'input[value="guest"]',
                'email_input'        => 'input[name="username"], #customer-email',
                'firstname'          => 'input[name="firstname"]',
                'lastname'           => 'input[name="lastname"]',
                'street'             => 'input[name="street[0]"]',
                'city'               => 'input[name="city"]',
                'postcode'           => 'input[name="postcode"]',
                'phone'              => 'input[name="telephone"]',
                'shipping_method'    => 'input[name="ko_unique_1"]',
                'payment_method'     => '#checkmo',
                'place_order_button' => 'button[type="submit"][x-bind*="submit"], button.action.primary.checkout',
                'success_heading'    => 'h1[x-text*="order"], .checkout-success h1',
            ],
            'account_registration' => [
                'register_form'       => 'form[action*="account/createpost"]',
                'firstname'           => 'input[name="firstname"]',
                'lastname'            => 'input[name="lastname"]',
                'email'               => 'input[name="email"]',
                'password'            => 'input[name="password"]',
                'password_confirm'    => 'input[name="password_confirmation"]',
                'submit_button'       => 'button[type="submit"]',
                'success_message'     => '.message-success',
                'account_nav'         => '[x-data*="account"], .nav.items',
            ],
            'auth' => [
                'login_form'     => 'form[action*="account/loginPost"]',
                'email_input'    => 'input[name="login[username]"]',
                'password_input' => 'input[name="login[password]"]',
                'sign_in_button' => 'button[type="submit"]',
                'account_link'   => '[x-data*="customer"] span, .customer-name',
                'logout_link'    => 'a[href*="customer/account/logout"]',
                'login_error'    => '.message-error',
            ],
            'search' => [
                'search_input'   => '[x-data*="search"] input[type="text"]',
                'search_button'  => '[x-data*="search"] button[type="submit"]',
                'results_title'  => 'main h1, .search-results h1',
                'product_item'   => '.product-item-info',
                'product_link'   => '.product-item-info a.product-item-photo',
                'no_results'     => '.message.notice',
            ],
        ];
    }

    private function getGenericSelectors(): array
    {
        return [
            'homepage' => [
                'nav_menu'          => 'nav',
                'search_input'      => 'input[type="search"], input[name="q"]',
                'cart_icon'         => '[href*="cart"]',
                'cart_count'        => '.cart-count',
                'hero_banner'       => '.hero, .banner',
                'featured_products' => '.products',
            ],
            'category' => [
                'category_title'  => 'h1',
                'product_item'    => '.product-item, .product-card',
                'product_link'    => '.product-item a, .product-card a',
                'sort_dropdown'   => 'select[name="sort"]',
                'filter_toggle'   => '.filter-toggle',
                'filter_option'   => '.filter-option',
                'pagination_next' => 'a[rel="next"]',
            ],
            'product' => [
                'product_title'   => 'h1',
                'product_price'   => '.price',
                'product_gallery' => '.product-gallery, .product-images',
                'qty_input'       => 'input[name="qty"], input[type="number"]',
                'product_sku'     => '.sku',
                'product_desc'    => '.description',
            ],
            'add_to_cart' => [
                'add_to_cart_button' => 'button[type="submit"], .add-to-cart',
                'cart_count'         => '.cart-count',
                'success_message'    => '.message-success, .alert-success',
            ],
            'cart' => [
                'cart_container'  => '.cart, .cart-page',
                'cart_item'       => '.cart-item',
                'qty_input'       => '.qty, input[name*="qty"]',
                'update_cart'     => '.update-cart',
                'remove_item'     => '.remove-item, .delete-item',
                'cart_subtotal'   => '.subtotal .price',
                'checkout_button' => 'a[href*="checkout"], .checkout-button',
            ],
            'guest_checkout' => [
                'guest_option'       => 'input[value="guest"]',
                'email_input'        => 'input[name="email"], input[type="email"]',
                'firstname'          => 'input[name="firstname"]',
                'lastname'           => 'input[name="lastname"]',
                'street'             => 'input[name="street"], input[name="address"]',
                'city'               => 'input[name="city"]',
                'postcode'           => 'input[name="postcode"], input[name="zip"]',
                'phone'              => 'input[name="telephone"], input[name="phone"]',
                'shipping_method'    => 'input[name="shipping_method"]',
                'payment_method'     => 'input[name="payment_method"]',
                'place_order_button' => 'button[type="submit"].checkout, .place-order',
                'success_heading'    => 'h1.success, .order-success h1',
            ],
            'account_registration' => [
                'register_form'       => 'form.register, form[action*="register"]',
                'firstname'           => 'input[name="firstname"]',
                'lastname'            => 'input[name="lastname"]',
                'email'               => 'input[name="email"]',
                'password'            => 'input[name="password"]',
                'password_confirm'    => 'input[name="password_confirmation"]',
                'submit_button'       => 'button[type="submit"]',
                'success_message'     => '.message-success, .alert-success',
                'account_nav'         => '.account-nav',
            ],
            'auth' => [
                'login_form'     => 'form[action*="login"]',
                'email_input'    => 'input[name="email"]',
                'password_input' => 'input[name="password"]',
                'sign_in_button' => 'button[type="submit"]',
                'account_link'   => '.account-link, .my-account',
                'logout_link'    => 'a[href*="logout"]',
                'login_error'    => '.message-error, .alert-danger',
            ],
            'search' => [
                'search_input'   => 'input[type="search"], input[name="q"]',
                'search_button'  => 'button[type="submit"]',
                'results_title'  => '.search-results h1, h1',
                'product_item'   => '.search-result-item, .product-item',
                'product_link'   => '.search-result-item a, .product-item a',
                'no_results'     => '.no-results, .empty-search',
            ],
        ];
    }
}
