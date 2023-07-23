<!DOCTYPE html>

<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="<?= base_url('admin/assets/') ?>" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title><?= $title ?></title>
    <?= csrf_meta() ?>

    <!-- Meta -->

    <meta name="description" content="Test" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('admin/assets/img/favicon/favicon.ico') ?>" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <!-- Icons -->
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/fonts/fontawesome.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/fonts/tabler-icons.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/fonts/flag-icons.css') ?>" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/css/rtl/core.css') ?>" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/css/rtl/theme-default.css') ?>" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?= base_url('admin/assets/css/demo.css') ?>" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/libs/node-waves/node-waves.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/libs/typeahead-js/typeahead.css') ?>" />
    <!-- Vendor -->
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/libs/formvalidation/dist/css/formValidation.min.css') ?>" />
    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="<?= base_url('admin/assets/vendor/css/pages/page-auth.css') ?>" />
    <!-- Helpers -->
    <script src="<?= base_url('admin/assets/vendor/js/helpers.js') ?>"></script>
    <script src="<?= base_url('admin/assets/vendor/js/template-customizer.js') ?>"></script>
    <script src="<?= base_url('admin/assets/js/config.js') ?>"></script>
</head>

<body>
    <!-- Content -->