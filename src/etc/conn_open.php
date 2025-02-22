<?php

try {
  $_conn = new PDO("mysql:host=". PRJ_DB_HOST .";dbname=". PRJ_DB_NAME, PRJ_DB_USER, PRJ_DB_PASS);
  $_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
  exit;
}