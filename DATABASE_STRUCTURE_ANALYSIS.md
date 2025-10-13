# Combs and Clippers Database Structure Analysis

## Overview
This document provides a comprehensive analysis of all database tables, their structures, and relationships based on Laravel migration files.

## Core Entity Tables

### 1. User Management
- **users** (Primary Key: id UUID)
  - Core user information: name, email, phone, password
  - Business fields: merchant_code, wallet_id, account_type, specialization
  - Status fields: accountstatus, email_verified
  - Authentication: sms_otp, token, remember_token

- **admins** (Primary Key: id UUID)
  - Admin user information: email, name, password
  - Role management: accounttype, role

- **social_providers** (Primary Key: id UUID)
  - Foreign Key: user_id → users.id
  - Social login integration: provider_id, provider, nickname, avatar

### 2. Subscription System
- **subscriptions** (Primary Key: id UUID)
  - Subscription plans: type, plan, description, price
  - Billing: currency, invoice_period, invoice_interval
  - Trial: trial_period, trial_interval
  - Payment: stripe_id

- **user_subscriptions** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, subscription_id → subscriptions.id
  - Status: active, auto_renew, expires_at
  - Payment tracking: ext_trans_id, internal_trans_id, status

## E-commerce Core Tables

### 3. Store Management
- **stores** (Primary Key: id UUID)
  - Foreign Key: merchant_id → users.id (integer)
  - Store info: store_name, store_category, store_sub_category
  - Branding: store_icon, store_banner, store_description
  - Settings: featured, approved, days_available, time_available

- **store_categories** (Primary Key: id UUID)
  - Category information: categoryname

- **store_sub_categories** (Primary Key: id UUID)
  - Foreign Key: store_category_id → store_categories.id
  - Sub-category: categoryname

- **user_stores** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, store_id → stores.id
  - Access control: approved, current

### 4. Product Management
- **products** (Primary Key: id UUID)
  - Foreign Keys: store_id → stores.id, merchant_id → users.id, category_id → categories.id, box_size_id → package_boxes.id
  - Product info: productname, description, product_slug, price, currency
  - Inventory: quantity, SKU, barcode, product_type
  - Media: image_url, other_images_url, video_link
  - Attributes: attributes (JSON), active, featured, recommended

- **categories** (Primary Key: id UUID)
  - Product categories: categoryname

- **product_photos** (Primary Key: id UUID)
  - Foreign Key: productID → products.id
  - Media: image_link

- **product_variants** (Primary Key: id UUID)
  - Foreign Key: product_id → products.id
  - Variant data: attributes (JSON), price, quantity, inStock

- **product_ratings** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, product_id → products.id
  - Rating: rating, title, description

### 5. Shopping Cart & Orders
- **carts** (Primary Key: id UUID)
  - Foreign Keys: buyer_id → users.id, merchant_id → users.id
  - Cart info: totalprice, status, items_count, currency
  - Delivery: max_delivery_period, min_delivery_period, shipping, delivery_type

- **cart_items** (Primary Key: id UUID)
  - Foreign Keys: cart_id → carts.id, productID → products.id
  - Item details: productname, quantity, price, total_cost, currency

- **orders** (Primary Key: id UUID)
  - Foreign Keys: merchant_id → users.id, buyer_id → users.id, address_id → addresses.id, cart_id → carts.id
  - Order info: orderRef, totalprice, shipping, total, currency
  - Payment: paymentRef, externalRef, payment_status, disbursement_status
  - Delivery: maxdeliverydate, mindeliverydate, delivery_type
  - Status: status, cancellation_reason

- **order_items** (Primary Key: id UUID)
  - Foreign Keys: order_id → orders.id, productID → products.id
  - Item details: productname, price, quantity, totalCost, image

## Service Booking System

### 6. Services
- **services** (Primary Key: id UUID)
  - Foreign Key: merchant_id → users.id
  - Service info: name, description, slug, price_type, price, currency
  - Booking settings: duration, buffer, payment_preference, deposit
  - Location: location, home_service_charge
  - Policies: allow_cancellation, allow_rescheduling, booking_reminder
  - Limits: limit_early_booking, limit_late_booking
  - Status: status, is_available

- **service_photos** (Primary Key: id UUID)
  - Foreign Key: service_id → services.id
  - Media: image_url

- **service_types** (Primary Key: id UUID)
  - Service categories: name

- **store_service_types** (Primary Key: id UUID)
  - Foreign Key: store_id → stores.id
  - Service types offered by store: service_type

### 7. Appointments
- **appointments** (Primary Key: id UUID)
  - Foreign Keys: store_id → stores.id, customer_id → users.id, merchant_id → users.id, address_id → addresses.id
  - Booking details: date, time, phone_number
  - Payment: payment_details, tip, total_amount, discount_amount, processing_fee
  - Status: status, payment_status, disbursement_status
  - Confirmation: merchant_confirmed_at, client_confirmed_at
  - Cancellation: cancel_reason, cancelled_by, cancelled_at

- **appointment_services** (Primary Key: id UUID)
  - Foreign Keys: appointment_id → appointments.id, service_id → services.id
  - Service details: quantity, price

## Financial System

### 8. Wallets & Transactions
- **wallets** (Primary Key: id UUID)
  - Foreign Key: user_id → users.id
  - Wallet info: wallet_number, account_number, bank_code, currency
  - Balance: amount, unclaimed_amount

- **wallet_transactions** (Primary Key: id UUID)
  - Foreign Keys: wallet_id → wallets.id, withdrawal_id → withdrawals.id
  - Transaction: type, transaction_ref, narration, amount, status
  - Account details: from_account_*, to_account_*

- **transactions** (Primary Key: id UUID)
  - Foreign Key: order_id → orders.id
  - Transaction: type, transcode, customer_email, merchant_email
  - Payment: amount, currency, payment_gateway, payment_status
  - Dates: posting_date, payment_date, startdate, enddate
  - Status: trans_status, refunded, extended, confirmed_by_merchant

- **payment_transactions** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, trans_id → transactions.id
  - Payment details: cust_email, trans_ref, amount, channel, card_type
  - Status: trans_status, gateway_res, paid_at_res

- **withdrawals** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, wallet_id → wallets.id
  - Withdrawal: amount, amount_requested, fee, narration
  - Bank details: account_number, account_name, bank_name, bank_code
  - Status: withdrawal_status, transferRef, is_internal

### 9. Invoicing
- **invoices** (Primary Key: id UUID)
  - Foreign Keys: merchant_id → users.id, customer_id → users.id
  - Invoice details: invoiceRef, paymentRef, totalcost, vat, currency
  - Status: status, confirmed, payment_status
  - Dates: startDate, endDate, deliveryPeriod

- **invoice_items** (Primary Key: id UUID)
  - Foreign Keys: invoiceID → invoices.id, productID → products.id
  - Item details: productname, quantity, unitPrice, totalCost, currency

- **invoice_files** (Primary Key: id UUID)
  - Foreign Key: invoice_id → invoices.id
  - Files: link, docType

## Dispute Resolution

### 10. Disputes
- **disputes** (Primary Key: id UUID)
  - Foreign Keys: order_id → orders.id, customer_id → users.id, merchant_id → users.id
  - Dispute details: dispute_referenceid, dispute_category, dispute_option
  - Description: dispute_description, comment
  - Status: dispute_status, resolution_date

- **dispute_files** (Primary Key: id UUID)
  - Foreign Key: dispute_id → disputes.id
  - Files: file_link

- **dispute_resolutions** (Primary Key: id UUID)
  - Foreign Key: dispute_id → disputes.id
  - Resolution: merchant_comment, customer_comment, arbitrator_comment
  - Process: resolution_desc, sitting_date, next_sitting_date

## Address Management

### 11. Addresses
- **addresses** (Primary Key: id UUID)
  - Foreign Key: recipient → users.id
  - Address details: name, street, city, state, postal_code, zip
  - Contact: phone, email
  - Location: longitude, latitude, address, formatted_address
  - Codes: address_code, city_code, state_code, country_code

- **pickup_addresses** (Primary Key: id UUID)
  - Foreign Key: merchant_id → users.id
  - Address details: name, street, city, state, country, zip
  - Contact: email, phone
  - Location: longitude, latitude, address, formatted_address

- **store_addresses** (Primary Key: id UUID)
  - Foreign Key: merchant_id → users.id
  - Store location: name, street, city, state, country, zip
  - Contact: email, phone
  - Location: longitude, latitude, address, formatted_address

## Booth Rental System

### 12. Booth Rentals
- **booth_rentals** (Primary Key: id UUID)
  - Foreign Keys: store_id → stores.id, user_id → users.id, service_type_id → service_types.id
  - Rental details: amount, payment_timeline, payment_days

- **booth_rental_payments** (Primary Key: id UUID)
  - Foreign Keys: user_store_id → user_stores.id, booth_rental_id → booth_rentals.id
  - Payment schedule: last_payment_date, next_payment_date, payment_status

- **booth_rent_payment_histories** (Primary Key: id UUID)
  - Foreign Key: booth_rent_payment_id → booth_rental_payments.id
  - Payment history: amount_paid, payment_date

## User Preferences & Social Features

### 13. User Interests & Reviews
- **interests** (Primary Key: id UUID)
  - Interest categories: name, image_link

- **user_interests** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, interest_id → interests.id
  - User preference tracking

- **reviews** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, merchant_id → users.id
  - Review: review_text, rating

- **favorite_stylists** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, stylist_id → users.id
  - User favorites tracking

### 14. Vouchers & Discounts
- **vouchers** (Primary Key: id UUID)
  - Foreign Keys: stylist_id → users.id, user_id → users.id
  - Voucher: code, discount, expiry_date, is_used

- **coupons** (Primary Key: id UUID)
  - Foreign Key: merchant_id → users.id
  - Coupon: code, discount_type, discount, start_date, end_date

- **coupon_usages** (Primary Key: id UUID)
  - Foreign Keys: user_id → users.id, coupon_id → coupons.id
  - Usage tracking

- **discounts** (Primary Key: id UUID)
  - Foreign Key: merchant_id → users.id
  - Discount: discount_name, discount_type, discount, start_date, end_date

- **discount_products** (Primary Key: id UUID)
  - Foreign Keys: discount_id → discounts.id, product_id → products.id
  - Product-discount relationship

## Additional Tables

### 15. Logistics & Delivery
- **order_logistics** (Primary Key: id UUID)
  - Foreign Keys: cart_id → carts.id, order_id → orders.id
  - Logistics: pickup_address_id, delivery_address_id
  - Tracking: pickup_order_id, fulfilment_request_id, delivery_status
  - Shipping: rate_id, kwik_key, estimated_days, amount

### 16. Payment Processing
- **payment_disbursements** (Primary Key: id UUID)
  - Foreign Key: order_id → orders.id
  - Disbursement: transferRef, traceID, fromAcc, toAcc, amount
  - Status: responseCode, responseMessage, statusMessage

### 17. User Banking
- **user_bank_details** (Primary Key: id UUID)
  - Foreign Key: user_id → users.id
  - Bank details: bank_name, account_number, routing_number, bank_code

### 18. Package Management
- **package_boxes** (Primary Key: id UUID)
  - Box details: box_size_id, name, description_image_url
  - Dimensions: height, width, length, max_weight

## Key Relationships Summary

### Primary Relationships
1. **User-Centric**: All major entities relate to users (customers/merchants)
2. **Store-Product**: Products belong to stores, stores belong to users
3. **Order-Item**: Orders contain multiple order items
4. **Service-Appointment**: Services are booked through appointments
5. **Financial Flow**: Wallets → Transactions → Payments → Disbursements

### Foreign Key Patterns
- Most tables use UUID primary keys
- User references are often integer-based (legacy)
- Store references are UUID-based
- Order references are UUID-based
- Many-to-many relationships use pivot tables

### Data Types
- **UUID**: Primary keys for most tables
- **Integer**: Legacy user IDs, some foreign keys
- **Decimal**: Monetary amounts (various precision)
- **JSON**: Complex data (attributes, availability)
- **Enum**: Status fields, payment types
- **Boolean**: Flags and status indicators

This database structure supports a comprehensive e-commerce and service booking platform with multi-tenant capabilities, financial management, and dispute resolution systems.
