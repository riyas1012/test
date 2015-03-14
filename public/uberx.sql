-- phpMyAdmin SQL Dump
-- version 4.2.8
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 24, 2014 at 08:27 AM
-- Server version: 5.6.19
-- PHP Version: 5.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `uberforxapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
`id` int(10) unsigned NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE IF NOT EXISTS `documents` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dog`
--

CREATE TABLE IF NOT EXISTS `dog` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `age` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `breed` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `likes` text COLLATE utf8_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `information`
--

CREATE TABLE IF NOT EXISTS `information` (
`id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ledger`
--

CREATE TABLE IF NOT EXISTS `ledger` (
`id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `referral_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `total_referrals` int(11) NOT NULL,
  `amount_earned` float(8,2) NOT NULL,
  `amount_spent` float(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE IF NOT EXISTS `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2014_09_25_044324_create_owners_table', 1),
('2014_09_25_060804_create_dogs_table', 1),
('2014_09_30_014129_create_walker_table', 1),
('2014_10_07_113949_create_review_dog_table', 1),
('2014_10_07_114245_create_review_walker_table', 1),
('2014_10_07_114415_create_walk_location_table', 1),
('2014_10_07_114726_create_walk_table', 1),
('2014_10_07_115301_create_schedule_table', 1),
('2014_10_07_115554_create_schedule_meta_table', 1),
('2014_10_10_025736_create_payment_table', 1),
('2014_10_11_144202_add_note_to_walk_table', 1),
('2014_10_13_024755_add_picture_to_walker_table', 1),
('2014_10_14_052816_add_walker_id_to_schedules_table', 1),
('2014_10_14_142220_add_fields_to_owner', 1),
('2014_10_14_142558_add_fields_to_walker', 1),
('2014_10_15_114904_add_lat_long_to_walker_table', 1),
('2014_10_15_115120_add_endson_seeding_to_meta_table', 1),
('2014_10_17_131510_add_is_confirmed_to_schedules_table', 1),
('2014_10_17_152616_add_meta_id_in_walk', 1),
('2014_10_18_051813_add_owner_id_to_payment', 1),
('2014_10_19_070302_create_request_table', 1),
('2014_10_19_070310_create_request_meta_table', 1),
('2014_10_20_084102_add_availability_on_job', 1),
('2014_10_20_084141_add_lat_long', 1),
('2014_10_20_085531_remove_schedule_id', 1),
('2014_10_20_102804_add_status_flags', 1),
('2014_10_21_013919_replace_walk_id_to_request_id', 1),
('2014_10_21_021438_replace_walk_id_to_reques_id_review_walker_table', 1),
('2014_10_21_021816_add_is_rated_in_walk', 1),
('2014_10_21_023844_replace_walk_id_to_reques_id_walk_location_table', 1),
('2014_10_23_033257_create_settings_table', 1),
('2014_10_24_050705_add_payment_fileds_to_request', 1),
('2014_10_27_112457_change_lat_long_data_type', 1),
('2014_10_27_112629_change_lat_long_data_type_walk_location', 1),
('2014_10_27_112915_add_lat_long_data_type_walker', 1),
('2014_10_27_112953_add_lat_long_data_type', 1),
('2014_11_01_015046_create_admin_table', 1),
('2014_11_01_015258_add_is_approved_to_walker', 1),
('2014_11_09_154756_add_information_table', 1),
('2014_11_09_181432_add_referal_data_to_owner', 1),
('2014_11_09_181525_add_ledger_table', 1),
('2014_11_10_035803_add_walker_type_table', 1),
('2014_11_10_040329_add_type_to_walker', 1),
('2014_11_13_064410_add_icon_to_type', 1),
('2014_11_13_064452_add_icon_to_info', 1),
('2014_11_17_052356_add_customerid', 1),
('2014_11_17_134313_add_paymen_split', 1),
('2014_11_18_111038_add_distance_walk_location', 1),
('2014_11_19_001415_change_value_datatype', 1),
('2014_11_19_001841_add_value_datatype', 1),
('2014_11_21_115919_remove_dog_id', 1),
('2014_11_21_115930_remove_dog_id_review', 1),
('2014_11_21_130810_add_is_cancelled_request', 1),
('2014_11_21_131108_add_is_cancelled', 1),
('2014_11_25_112910_add_tip_page', 1),
('2014_11_26_025409_add_last_four', 1),
('2014_12_03_170427_add_foreign_key_dog', 1),
('2014_12_03_171436_add_foreign_key_ledger', 1),
('2014_12_03_171732_add_foreign_key_payment', 1),
('2014_12_03_172008_add_foreign_key_request', 1),
('2014_12_03_172703_add_foreign_key_request_meta', 1),
('2014_12_03_172949_add_foreign_key_review_dog', 1),
('2014_12_03_173126_add_foreign_key_review_dog_2', 1),
('2014_12_03_173221_add_foreign_key_review_dog_3', 1),
('2014_12_03_174014_add_foreign_key_review_walker', 1),
('2014_12_03_174427_add_foreign_key_walk_location', 1),
('2014_12_08_121851_add_documents_table', 1),
('2014_12_08_130512_add_document_type_table', 1),
('2014_12_14_114805_add_type_to_request', 1),
('2014_12_17_132347_update_walker_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE IF NOT EXISTS `owner` (
`id` int(10) unsigned NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bio` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` text COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dog_id` int(11) NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token_expiry` int(11) NOT NULL,
  `device_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `device_type` enum('android','ios') COLLATE utf8_unicode_ci NOT NULL,
  `login_by` enum('manual','facebook','google') COLLATE utf8_unicode_ci NOT NULL,
  `social_unique_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `latitude` double(15,8) NOT NULL,
  `longitude` double(15,8) NOT NULL,
  `referred_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
`id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `owner_id` int(10) unsigned NOT NULL,
  `customer_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_four` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE IF NOT EXISTS `request` (
`id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `status` int(11) NOT NULL,
  `confirmed_walker` int(11) NOT NULL,
  `current_walker` int(11) NOT NULL,
  `request_start_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_walker_started` int(11) NOT NULL,
  `is_walker_arrived` int(11) NOT NULL,
  `is_started` int(11) NOT NULL,
  `is_completed` int(11) NOT NULL,
  `is_dog_rated` int(11) NOT NULL,
  `is_walker_rated` int(11) NOT NULL,
  `distance` float(8,2) NOT NULL,
  `time` float(8,2) NOT NULL,
  `base_price` float(8,2) NOT NULL,
  `distance_cost` float(8,2) NOT NULL,
  `time_cost` float(8,2) NOT NULL,
  `total` float(8,2) NOT NULL,
  `is_paid` int(11) NOT NULL,
  `card_payment` float(8,2) NOT NULL,
  `ledger_payment` float(8,2) NOT NULL,
  `is_cancelled` int(11) NOT NULL,
  `type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_meta`
--

CREATE TABLE IF NOT EXISTS `request_meta` (
`id` int(10) unsigned NOT NULL,
  `request_id` int(10) unsigned NOT NULL,
  `walker_id` int(10) unsigned NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_cancelled` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_dog`
--

CREATE TABLE IF NOT EXISTS `review_dog` (
`id` int(10) unsigned NOT NULL,
  `walker_id` int(10) unsigned NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `request_id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_walker`
--

CREATE TABLE IF NOT EXISTS `review_walker` (
`id` int(10) unsigned NOT NULL,
  `walker_id` int(10) unsigned NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `request_id` int(10) unsigned NOT NULL,
  `owner_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE IF NOT EXISTS `schedules` (
`id` int(10) unsigned NOT NULL,
  `dog_id` int(11) NOT NULL,
  `lockbox_info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_recurring` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `walker_id` int(11) NOT NULL,
  `is_confirmed` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_meta`
--

CREATE TABLE IF NOT EXISTS `schedule_meta` (
`id` int(10) unsigned NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `ends_on` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `started_on` float(8,2) NOT NULL,
  `seeding_status` float(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
`id` int(10) unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tool_tip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `page` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `created_at`, `updated_at`, `value`, `tool_tip`, `page`) VALUES
(1, 'default_search_radius', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '5', 'Defalt search radius to look for providers', 1),
(2, 'default_charging_method_for_users', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '1', 'Default Changing method for users', 1),
(3, 'base_price', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '50', 'Incase of Fixed price payment, Base price is the total amount thats charged to users', 1),
(4, 'price_per_unit_distance', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '10', 'Needed only incase of time and distance based payment', 1),
(5, 'price_per_unit_time', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '8', 'Needed only incase of time and distance based payment', 1),
(6, 'provider_timeout', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '60', 'Maximum time for provider to respond for a request', 1),
(7, 'sms_notification', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '0', 'Send SMS Notifications', 1),
(8, 'email_notification', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '0', 'Send Email Notifications', 1),
(9, 'push_notification', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '1', 'Send Push Notifications', 1),
(10, 'default_referral_bonus', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '10', 'Bonus credit that should be added incase if user refers another', 1),
(11, 'admin_phone_number', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '+917708288018', 'This mobile number will get SMS notifications about requests', 1),
(12, 'admin_email_address', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'prabakaranbs@gmail.com', 'This address will get Email notifications about requests', 1),
(13, 'sms_when_provider_accepts', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Hi %user%, Your request is accepted by %driver%. You can reach him by %driver_mobile%', 'This Template will be used to notify user by SMS when a provider the accepts request', 2),
(14, 'sms_when_provider_arrives', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Hi %user%, The %driver% has arrived at your location.You can reach user by %driver_mobile%', 'This Template will be used to notify user by SMS when a provider the arrives', 2),
(15, 'sms_when_provider_completes_job', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Hi %user%, Your request is successfully completed by %driver%. Your Bill amount id %amount%', 'This Template will be used to notify user by SMS when a provider the completes the service', 2),
(16, 'sms_request_created', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Request id %id% is created by %user%, You can reach him by %user_mobile%', 'This Template will be used to notify admin by SMS when a new request is created', 2),
(17, 'sms_request_unanswered', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Request id %id% created by %user% is left unanswered, You can reach user by %user_mobile%', 'This Template will be used to notify admin by SMS when a request remains unanswered by all providers', 2),
(18, 'sms_request_completed', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Request id %id% created by %user% is completed, You can reach user by %user_mobile%', 'This Template will be used to notify admin by SMS when a request is completed', 2),
(19, 'sms_payment_generated', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Payment for Request id %id% is generated.', 'This Template will be used to notify admin by SMS when payment is generated for a request', 2),
(20, 'email_forgot_password', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Your New Password is %password%. Please dont forget to change the password once you log in next time.', 'This Template will be used to notify users and providers by email when they reset their password', 3),
(21, 'email_walker_new_registration', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Welcome on Board %name%', 'This Template will be used for welcome mail to provider', 3),
(22, 'email_owner_new_registration', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Welcome on Board %name%', 'This Template will be used for welcome mail to user', 3),
(23, 'email_new_request', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'New Requeest %id% is created. Follow the request through %url%', 'This Template will be used notify admin by email when a new request is created', 3),
(24, 'email_request_unanswered', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Requeest %id% has beed declined by all providers. Follow the request through %url%', 'This Template will be used notify admin by email when a request remains unanswerd by all providers', 3),
(25, 'email_request_finished', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Requeest %id% is finished. Follow the request through %url%', 'This Template will be used notify admin by email when a request is completed', 3),
(26, 'email_payment_charged', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'Requeest %id% is finished. Follow the request through %url%', 'This Template will be used notify admin by email when a client is charged for a request', 3),
(27, 'email_invoice_generated_user', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'invoice for Request id %id% is generated. Total amount is %amount%', 'This Template will be used notify user by email when invoice is generated', 3),
(28, 'email_invoice_generated_provider', '2014-12-24 02:57:25', '2014-12-24 02:57:25', 'invoice for Request id %id% is generated. Total amount is %amount%', 'This Template will be used notify provider by email when invoice is generated', 3),
(29, 'map_center_latitude', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '0', 'This is latitude for the map center', 1),
(30, 'map_center_longitude', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '0', 'This is longitude for the map center', 1),
(31, 'default_distance_unit', '2014-12-24 02:57:25', '2014-12-24 02:57:25', '0', 'This is the default unit of distance', 1);

-- --------------------------------------------------------

--
-- Table structure for table `walk`
--

CREATE TABLE IF NOT EXISTS `walk` (
`id` int(10) unsigned NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `dog_id` int(11) NOT NULL,
  `walker_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `is_walker_rated` int(11) NOT NULL,
  `is_dog_rated` int(11) NOT NULL,
  `is_confirmed` int(11) NOT NULL,
  `is_started` int(11) NOT NULL,
  `is_completed` int(11) NOT NULL,
  `is_cancelled` int(11) NOT NULL,
  `distance` float(8,2) NOT NULL,
  `time` int(11) NOT NULL,
  `is_poo` int(11) NOT NULL,
  `is_pee` int(11) NOT NULL,
  `photo_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `video_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `note` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `walker`
--

CREATE TABLE IF NOT EXISTS `walker` (
`id` int(10) unsigned NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bio` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `device_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `device_type` enum('android','ios') COLLATE utf8_unicode_ci NOT NULL,
  `login_by` enum('manual','facebook','google') COLLATE utf8_unicode_ci NOT NULL,
  `social_unique_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token_expiry` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_active` int(11) NOT NULL,
  `is_available` int(11) NOT NULL,
  `latitude` double(15,8) NOT NULL,
  `longitude` double(15,8) NOT NULL,
  `is_approved` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `walker_documents`
--

CREATE TABLE IF NOT EXISTS `walker_documents` (
`id` int(10) unsigned NOT NULL,
  `walker_id` int(11) NOT NULL,
  `document_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `walker_type`
--

CREATE TABLE IF NOT EXISTS `walker_type` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_default` int(11) NOT NULL,
  `price_per_unit_distance` float(8,2) NOT NULL,
  `price_per_unit_time` float(8,2) NOT NULL,
  `base_price` float(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `walk_location`
--

CREATE TABLE IF NOT EXISTS `walk_location` (
`id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `request_id` int(10) unsigned NOT NULL,
  `latitude` double(15,8) NOT NULL,
  `longitude` double(15,8) NOT NULL,
  `distance` float(8,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dog`
--
ALTER TABLE `dog`
 ADD PRIMARY KEY (`id`), ADD KEY `dog_owner_id_foreign` (`owner_id`);

--
-- Indexes for table `information`
--
ALTER TABLE `information`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ledger`
--
ALTER TABLE `ledger`
 ADD PRIMARY KEY (`id`), ADD KEY `ledger_owner_id_foreign` (`owner_id`);

--
-- Indexes for table `owner`
--
ALTER TABLE `owner`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
 ADD PRIMARY KEY (`id`), ADD KEY `payment_owner_id_foreign` (`owner_id`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
 ADD PRIMARY KEY (`id`), ADD KEY `request_owner_id_foreign` (`owner_id`);

--
-- Indexes for table `request_meta`
--
ALTER TABLE `request_meta`
 ADD PRIMARY KEY (`id`), ADD KEY `request_meta_request_id_foreign` (`request_id`), ADD KEY `request_meta_walker_id_foreign` (`walker_id`);

--
-- Indexes for table `review_dog`
--
ALTER TABLE `review_dog`
 ADD PRIMARY KEY (`id`), ADD KEY `review_dog_owner_id_foreign` (`owner_id`), ADD KEY `review_dog_walker_id_foreign` (`walker_id`), ADD KEY `review_dog_request_id_foreign` (`request_id`);

--
-- Indexes for table `review_walker`
--
ALTER TABLE `review_walker`
 ADD PRIMARY KEY (`id`), ADD KEY `review_walker_owner_id_foreign` (`owner_id`), ADD KEY `review_walker_walker_id_foreign` (`walker_id`), ADD KEY `review_walker_request_id_foreign` (`request_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedule_meta`
--
ALTER TABLE `schedule_meta`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walk`
--
ALTER TABLE `walk`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walker`
--
ALTER TABLE `walker`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walker_documents`
--
ALTER TABLE `walker_documents`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walker_type`
--
ALTER TABLE `walker_type`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walk_location`
--
ALTER TABLE `walk_location`
 ADD PRIMARY KEY (`id`), ADD KEY `walk_location_request_id_foreign` (`request_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `dog`
--
ALTER TABLE `dog`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `information`
--
ALTER TABLE `information`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ledger`
--
ALTER TABLE `ledger`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `owner`
--
ALTER TABLE `owner`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `request_meta`
--
ALTER TABLE `request_meta`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `review_dog`
--
ALTER TABLE `review_dog`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `review_walker`
--
ALTER TABLE `review_walker`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `schedule_meta`
--
ALTER TABLE `schedule_meta`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=32;
--
-- AUTO_INCREMENT for table `walk`
--
ALTER TABLE `walk`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `walker`
--
ALTER TABLE `walker`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `walker_documents`
--
ALTER TABLE `walker_documents`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `walker_type`
--
ALTER TABLE `walker_type`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `walk_location`
--
ALTER TABLE `walk_location`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `dog`
--
ALTER TABLE `dog`
ADD CONSTRAINT `dog_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`id`);

--
-- Constraints for table `ledger`
--
ALTER TABLE `ledger`
ADD CONSTRAINT `ledger_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
ADD CONSTRAINT `payment_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`id`);

--
-- Constraints for table `request`
--
ALTER TABLE `request`
ADD CONSTRAINT `request_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`id`);

--
-- Constraints for table `request_meta`
--
ALTER TABLE `request_meta`
ADD CONSTRAINT `request_meta_walker_id_foreign` FOREIGN KEY (`walker_id`) REFERENCES `walker` (`id`),
ADD CONSTRAINT `request_meta_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`);

--
-- Constraints for table `review_dog`
--
ALTER TABLE `review_dog`
ADD CONSTRAINT `review_dog_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`),
ADD CONSTRAINT `review_dog_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`id`),
ADD CONSTRAINT `review_dog_walker_id_foreign` FOREIGN KEY (`walker_id`) REFERENCES `walker` (`id`);

--
-- Constraints for table `review_walker`
--
ALTER TABLE `review_walker`
ADD CONSTRAINT `review_walker_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`),
ADD CONSTRAINT `review_walker_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owner` (`id`),
ADD CONSTRAINT `review_walker_walker_id_foreign` FOREIGN KEY (`walker_id`) REFERENCES `walker` (`id`);

--
-- Constraints for table `walk_location`
--
ALTER TABLE `walk_location`
ADD CONSTRAINT `walk_location_request_id_foreign` FOREIGN KEY (`request_id`) REFERENCES `request` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
