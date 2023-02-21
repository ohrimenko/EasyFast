CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` float NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_sort_index` (`sort`);

ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE `areas` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` float NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `areas_sort_index` (`sort`);
  
ALTER TABLE `areas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE `urls` (
  `id` int(10) UNSIGNED NOT NULL,
  `area_id` int(10) UNSIGNED NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` float NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `urls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `urls_sort_index` (`sort`),
  ADD KEY `urls_area_id_index` (`area_id`);
  
ALTER TABLE `urls`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
ALTER TABLE `urls`
  ADD CONSTRAINT `urls_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`);
  
ALTER TABLE `products` 
  ADD `area_id` int(10) UNSIGNED DEFAULT NULL AFTER `id`;
  
ALTER TABLE `products`
  ADD KEY `products_area_id_index` (`area_id`);

ALTER TABLE `products`
  ADD CONSTRAINT `products_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`); 
  
ALTER TABLE `products` 
  ADD `url_id` int(10) UNSIGNED DEFAULT NULL AFTER `area_id`;
  
ALTER TABLE `products`
  ADD KEY `products_url_id_index` (`url_id`);

ALTER TABLE `products`
  ADD CONSTRAINT `products_url_id_foreign` FOREIGN KEY (`url_id`) REFERENCES `urls` (`id`); 

CREATE TABLE `typefields` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` float NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `typefields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `typefields_sort_index` (`sort`);
  
ALTER TABLE `typefields`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE `fields` (
  `id` int(10) UNSIGNED NOT NULL,
  `typefield_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NULL DEFAULT NULL,
  `value` varchar(255) NULL DEFAULT NULL,
  `content` text NULL DEFAULT NULL,
  `trans` varchar(255) NULL DEFAULT NULL,
  `sort` float NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fields_sort_index` (`sort`),
  ADD KEY `fields_typefield_id_index` (`typefield_id`),
  ADD KEY `fields_currency_id_index` (`product_id`);

ALTER TABLE `fields`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `fields`
  ADD CONSTRAINT `fields_typefield_id_foreign` FOREIGN KEY (`typefield_id`) REFERENCES `typefields` (`id`); 

ALTER TABLE `fields`
  ADD CONSTRAINT `fields_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`); 

ALTER TABLE `products`
  ADD KEY `products_pid_unique` (`pid`);

ALTER TABLE `products`
  ADD UNIQUE KEY `products_pid_area_id_unique` (`pid`, `area_id`);

CREATE TABLE `currencies` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trans` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort` float NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `currencies_sort_index` (`sort`);

ALTER TABLE `currencies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `urls` 
  ADD `data` text NULL DEFAULT NULL;
