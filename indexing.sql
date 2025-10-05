ALTER TABLE addresses
ADD INDEX (street, state, city);

ALTER TABLE pickup_addresses
ADD INDEX (street, state, city);

ALTER TABLE store_addresses
ADD INDEX (street, state, city);

ALTER TABLE activities
ADD INDEX (model, description, controller, `action`);

ALTER TABLE admins
ADD UNIQUE INDEX (email);

ALTER TABLE billing_invoices
ADD INDEX (invoice_number);


ALTER TABLE cards
ADD INDEX (card_number, cvv);

ALTER TABLE cart_items
ADD INDEX (cart_id, productID);

ALTER TABLE carts
ADD INDEX (buyer_id, merchant_id, `status`, `delivery_type`);

ALTER TABLE categories
ADD INDEX (categoryname);

ALTER TABLE cities
ADD INDEX (`name`);

ALTER TABLE countries
ADD INDEX (country);

ALTER TABLE currencies
ADD INDEX (`name`, currency);

ALTER TABLE dispute_files
ADD INDEX (dispute_id);

ALTER TABLE disputes
ADD INDEX (order_id, customer_id, merchant_id, customer_email, merchant_email, dispute_referenceid);

ALTER TABLE menus
ADD INDEX (`name`);

ALTER TABLE momo_transactions
ADD INDEX (model, model_uid, int_ref, ext_ref, `status`);

ALTER TABLE notification_settings
ADD INDEX (user_id);

ALTER TABLE order_items
ADD INDEX (order_id, productID);

ALTER TABLE order_logistics
ADD INDEX (cart_id, order_id, pickup_order_id, fulfilment_request_id);

ALTER TABLE orders
ADD INDEX (buyer_id, merchant_id, paymentRef, externalRef, orderRef, `status`, delivery_type);

ALTER TABLE payment_transactions
ADD INDEX (user_id, trans_id, cust_id, cust_email);

ALTER TABLE pending_payments
ADD INDEX (initiated_by, reference);

ALTER TABLE product_photos
ADD INDEX (productID);


ALTER TABLE product_requests
ADD INDEX (product_name, product_category, email);

ALTER TABLE products
ADD INDEX (store_id, merchant_id, merchant_email, productname, product_slug, product_code);

ALTER TABLE products
ADD INDEX sendy(sendy_product_id, sendy_variant_id);

ALTER TABLE refunds
ADD INDEX (order_id, transaction_ref, `status`);


ALTER TABLE social_providers
ADD INDEX (user_id, provider_id, provider);


ALTER TABLE store_categories
ADD INDEX (categoryname);

ALTER TABLE store_visits
ADD INDEX (merchant_id, store_id);

ALTER TABLE stores
ADD INDEX (merchant_id, store_name, store_category);

ALTER TABLE subscriptions
ADD INDEX (plan, service_code);

ALTER TABLE transactions
ADD INDEX (merchant_code, order_id, transcode, customer_email, merchant_email);

ALTER TABLE transactions_history
ADD INDEX (transcode, customer_email, merchant_email);

ALTER TABLE user_subscriptions
ADD INDEX (user_id, subscription_id, ext_trans_id, internal_trans_id, `status`);

ALTER TABLE users
ADD INDEX users_frequently_q(merchant_code, phone, referral_code);

ALTER TABLE users
ADD INDEX users_names(`name`, firstName, lastName);

ALTER TABLE waitlists
ADD UNIQUE INDEX (referral_code, email);

ALTER TABLE withdrawals
ADD INDEX (merchant_id, transaction_ref, `status`);