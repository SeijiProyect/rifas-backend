<?php

return array (
  'APP_ENV' => 'dev',
  'APP_SECRET' => '16b53ce8e380a51434f97b676c44b85b',
  'DATABASE_URL' => 'sqlite:///' . __DIR__ . '/var/data.db',
  'CORS_ALLOW_ORIGIN' => '^https?://(localhost:4200|127\\.0\\.0\\.1:4200|admin-rifas\\.ct\\.ws|user-rifas\\.ct\\.ws|formularioinscripcion\\.detoqueytoque\\.com)(:[0-9]+)?$',
  'MAILER_DSN' => 'null://localhost',
  'APP_DEBUG' => 'true',
  'URL_ENV_API' => 'http://localhost:8000/',
  'URL_RAIZ_SERVER' => 'http://localhost:8000',
  'URL_LINK_PAGO' => 'http://localhost:4200',
  'URL_ENV_FRONT_RIFA' => 'http://localhost:4200',
  'URL_ENV_FRONT_ADMIN' => 'http://localhost:4201',
);
