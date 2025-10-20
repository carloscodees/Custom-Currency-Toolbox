# WooCommerce Currency Toolbox

A complete WooCommerce plugin that allows users to switch store currencies with support for automatic exchange rates, IP-based detection, and multiple pricing zones.

---

## 🧭 Table of Contents
- [Key Features](#key-features)
  - [For Administrators](#for-administrators)
  - [For Frontend Users](#for-frontend-users)
- [Installation](#installation)
- [Configuration](#configuration)
  - [General Settings](#1-general-settings)
  - [Currency Management](#2-currency-management)
  - [Exchange Rates](#3-exchange-rates)
  - [Pricing Zones](#4-pricing-zones)
  - [Widget and Shortcode](#5-widget-and-shortcode)
- [Usage](#usage)
- [Hooks and Filters](#hooks-and-filters)
- [WPML Integration](#wpml-integration)
- [Pricing Zones](#pricing-zones)
- [Currency-Based Coupons](#currency-based-coupons)
- [Exchange Rate APIs](#exchange-rate-apis)
- [Customization](#customization)
- [Troubleshooting](#troubleshooting)
- [Support](#support)
- [Changelog](#changelog)
- [Translations](#translations)
- [License](#license)

---

## 🧩 Key Features

### For Administrators
- ✅ Configure multiple currencies from the backend  
- ✅ Manual and automatic exchange rates via API  
- ✅ Integration with Fixer.io and ExchangeRate-API  
- ✅ Option to display country flags  
- ✅ Control purchases in the selected currency  
- ✅ Customizable currency switcher widget  
- ✅ Price zones based on user location  
- ✅ Coupon system with currency restrictions  
- ✅ Full WPML (multi-language) integration  

### For Frontend Users
- ✅ Currency switching on shop pages  
- ✅ Currency switching in the cart  
- ✅ Currency switching at checkout  
- ✅ Automatic currency detection by IP  
- ✅ Multiple widget styles (dropdown, buttons, flags)  
- ✅ Automatic price conversion  
- ✅ Support for simple and variable products  

---

## ⚙️ Installation

1. Upload the `woocommerce-currency-toolbox` folder to `/wp-content/plugins/`  
2. Activate the plugin from the **WordPress Admin Panel**  
3. Go to **Currency Switcher** in the admin menu  
4. Configure currencies and exchange rates  

---

## 🛠️ Configuration

### 1. General Settings
- **Base Currency:** Set the default currency for all calculations  
- **Show Flags:** Enable or disable country flags  
- **IP Detection:** Automatically detect user currency based on IP  
- **Allow Purchases in Selected Currency:** Choose if checkout processes in the selected currency  

### 2. Currency Management
- Add, edit, and remove currencies  
- Configure currency symbols and price formats  
- Set manual exchange rates  
- Assign allowed payment methods per currency  

### 3. Exchange Rates
- **Manual:** Set fixed exchange rates  
- **API Integration:** Connect with  
  - Fixer.io  
  - ExchangeRate-API  
- Schedule automatic updates  

### 4. Pricing Zones
- Create pricing zones by geographic location  
- Assign currencies to each zone  
- Set individual product prices per zone  

### 5. Widget and Shortcode
- **Position:** Header, Footer, Shop, Cart, Checkout  
- **Style:** Dropdown, Buttons, Flags  
- **Shortcode:** `[wct_currency_switcher]`

---

## 💻 Usage

### Shortcode Example
```php
// Basic
[wct_currency_switcher]

// With parameters
[wct_currency_switcher style="buttons" show_flags="yes" currencies="USD,EUR,GBP"]
