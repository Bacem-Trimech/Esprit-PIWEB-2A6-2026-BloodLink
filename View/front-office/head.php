<?php /* Shared <head>. Needs $title. theme.js applies the saved day/night mode before the first paint. */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(($title ?? APP_NAME) . ' | ' . APP_NAME) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <script src="<?= asset('js/theme.js') ?>"></script>
  <noscript><style>#spinner{display:none}.reveal{opacity:1;transform:none}</style></noscript>
</head>
<body>
