-- ===========================================================================
-- Български библиотечни изисквания: инвентарна книга, КДБФ, отчисляване,
-- допълнителни полета по ISBD, индекс за търсене на кирилица.
--
-- Основания: Наредба № 3 от 18.11.2014 г. на МК (чл. 13–17, 26–41),
-- ISBD (консолидирано издание, ББИА 2012), COMARC/H 99X d (сигнатура).
--
-- Зарежда се автоматично при нова инсталация (docker-compose.yml → 04-bg_catalog.sql).
-- За вече работеща база (може да се пуска многократно – не губи данни):
--   docker exec -i db sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" "$MARIADB_DATABASE"' < install/bg_catalog.sql
-- След това: Система → Индексиране → „Изпразни индекса“ → „Пресъздай индекса“.
-- ===========================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- 1. Екземпляри (инвентарна книга, чл. 16)
-- ---------------------------------------------------------------------------
ALTER TABLE item MODIFY COLUMN price DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE item
  ADD COLUMN IF NOT EXISTS volume          VARCHAR(30) NULL DEFAULT NULL AFTER call_number,
  ADD COLUMN IF NOT EXISTS item_notes      TEXT        NULL DEFAULT NULL AFTER invoice_date,
  ADD COLUMN IF NOT EXISTS kdbf_entry_id   INT(11)     NULL DEFAULT NULL AFTER item_notes,
  ADD COLUMN IF NOT EXISTS kdbf_legacy_ref VARCHAR(40) NULL DEFAULT NULL AFTER kdbf_entry_id,
  ADD COLUMN IF NOT EXISTS writeoff_id     INT(11)     NULL DEFAULT NULL AFTER kdbf_legacy_ref,
  ADD KEY IF NOT EXISTS kdbf_entry_idx (kdbf_entry_id),
  ADD KEY IF NOT EXISTS writeoff_idx (writeoff_id),
  ADD KEY IF NOT EXISTS received_date_idx (received_date);
-- инвентарният номер е самият баркод (item_code); inventory_code се пази като копие
UPDATE item SET inventory_code = item_code
 WHERE item_code IS NOT NULL AND (inventory_code IS NULL OR inventory_code <> item_code);

-- ---------------------------------------------------------------------------
-- 2. Библиографски запис (ISBD) и свързаните с него колони
-- ---------------------------------------------------------------------------
ALTER TABLE biblio MODIFY COLUMN isbn_issn VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE biblio MODIFY COLUMN collation VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE biblio MODIFY COLUMN classification VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE biblio ALTER COLUMN language_id SET DEFAULT 'bg';
ALTER TABLE biblio
  ADD COLUMN IF NOT EXISTS price_terms   VARCHAR(100) NULL DEFAULT NULL AFTER isbn_issn,
  ADD COLUMN IF NOT EXISTS printing      VARCHAR(150) NULL DEFAULT NULL AFTER publish_place_id,
  ADD COLUMN IF NOT EXISTS series_number VARCHAR(20)  NULL DEFAULT NULL AFTER series_title,
  ADD COLUMN IF NOT EXISTS author_mark   VARCHAR(10)  NULL DEFAULT NULL AFTER call_number;
ALTER TABLE mst_place MODIFY COLUMN place_name VARCHAR(100) NOT NULL;
ALTER TABLE stock_take_item MODIFY COLUMN classification VARCHAR(100) NULL DEFAULT NULL;

-- MyISAM позволява до 1000 байта на индекс – съставният индекс се пресъздава с префикси
ALTER TABLE search_biblio DROP KEY IF EXISTS add_indexes;
ALTER TABLE search_biblio MODIFY COLUMN isbn_issn VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE search_biblio MODIFY COLUMN classification VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE search_biblio MODIFY COLUMN publish_place VARCHAR(100) NULL DEFAULT NULL;
ALTER TABLE search_biblio ADD KEY add_indexes (gmd, publisher(50), publish_place(30), language, classification(40), publish_year, call_number);
-- нормализиран текст за търсене на кирилица (попълва се от индексатора)
ALTER TABLE search_biblio
  ADD COLUMN IF NOT EXISTS n_title     TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_author    TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_subject   TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_publisher TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_place     TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_series    TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_notes     TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_origtitle TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_class     TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_isbn      TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS n_all       TEXT NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS year_int    SMALLINT NULL DEFAULT NULL,
  ADD KEY IF NOT EXISTS idx_year_int (year_int);

-- ---------------------------------------------------------------------------
-- 3. КДБФ част 1 – постъпления (чл. 14–15; № започва от 1 всяка година)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kdbf_entry (
  entry_id      INT(11)       NOT NULL AUTO_INCREMENT,
  entry_year    SMALLINT(4)   NOT NULL,
  entry_no      INT(11)       NOT NULL,
  entry_date    DATE          NOT NULL,
  acq_method    CHAR(3)       NOT NULL DEFAULT 'BUY',
  source_name   VARCHAR(200)  DEFAULT NULL,
  supplier_id   INT(11)       DEFAULT NULL,
  doc_type      VARCHAR(50)   DEFAULT NULL,
  doc_no        VARCHAR(30)   DEFAULT NULL,
  doc_date      DATE          DEFAULT NULL,
  total_count   INT(11)       NOT NULL DEFAULT 0,
  total_value   DECIMAL(12,2) DEFAULT NULL,
  currency      CHAR(3)       NOT NULL DEFAULT 'EUR',
  noninv_count  INT(11)       NOT NULL DEFAULT 0,
  noninv_value  DECIMAL(12,2) DEFAULT NULL,
  notes         TEXT          DEFAULT NULL,
  input_date    DATETIME      NOT NULL,
  last_update   DATETIME      DEFAULT NULL,
  uid           INT(11)       DEFAULT NULL,
  PRIMARY KEY (entry_id),
  UNIQUE KEY entry_year_no (entry_year, entry_no),
  KEY entry_date (entry_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ---------------------------------------------------------------------------
-- 4. КДБФ част 2 – актове за отчисляване (чл. 30–39; една причина на акт)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kdbf_writeoff (
  writeoff_id     INT(11)       NOT NULL AUTO_INCREMENT,
  act_year        SMALLINT(4)   NOT NULL,
  act_no          INT(11)       NOT NULL,
  act_date        DATE          NOT NULL,
  reason          CHAR(3)       NOT NULL,
  reason_details  VARCHAR(255)  DEFAULT NULL,
  commission      TEXT          DEFAULT NULL,
  approved_by     VARCHAR(100)  DEFAULT NULL,
  approved_date   DATE          DEFAULT NULL,
  status          ENUM('draft','approved','cancelled') NOT NULL DEFAULT 'draft',
  kdbf_no         INT(11)       DEFAULT NULL,
  kdbf_date       DATE          DEFAULT NULL,
  total_count     INT(11)       DEFAULT NULL,
  total_value_bgn DECIMAL(14,2) DEFAULT NULL,
  total_value_eur DECIMAL(14,2) DEFAULT NULL,
  noninv_count    INT(11)       NOT NULL DEFAULT 0,
  noninv_value    DECIMAL(12,2) DEFAULT NULL,
  stock_take_id   INT(11)       DEFAULT NULL,
  notes           TEXT          DEFAULT NULL,
  input_date      DATETIME      NOT NULL,
  last_update     DATETIME      DEFAULT NULL,
  uid             INT(11)       DEFAULT NULL,
  PRIMARY KEY (writeoff_id),
  UNIQUE KEY act_year_no (act_year, act_no),
  UNIQUE KEY kdbf_year_no (act_year, kdbf_no),
  KEY act_date (act_date),
  KEY status (status)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ---------------------------------------------------------------------------
-- 5. КДБФ част 3 – наличност в началото на годината
--    (ръчно за първата година; 'closing' при приключване на година)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kdbf_year_balance (
  balance_year  SMALLINT(4)   NOT NULL,
  opening_count INT(11)       NOT NULL DEFAULT 0,
  opening_value DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  currency      CHAR(3)       NOT NULL DEFAULT 'BGN',
  source        ENUM('manual','closing') NOT NULL DEFAULT 'manual',
  notes         TEXT          DEFAULT NULL,
  last_update   DATETIME      DEFAULT NULL,
  uid           INT(11)       DEFAULT NULL,
  PRIMARY KEY (balance_year)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ---------------------------------------------------------------------------
-- 6. Отметки при проверка (история на инвентаризациите, чл. 40–41)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS item_check_log (
  check_id      INT(11)     NOT NULL AUTO_INCREMENT,
  item_id       INT(11)     NOT NULL,
  item_code     VARCHAR(20) NOT NULL,
  stock_take_id INT(11)     DEFAULT NULL,
  check_date    DATE        NOT NULL,
  result        ENUM('found','on_loan','missing') NOT NULL,
  checked_by    VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (check_id),
  UNIQUE KEY stock_take_item (stock_take_id, item_id),
  KEY item_id (item_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- ---------------------------------------------------------------------------
-- 7. Статуси на екземплярите (rules: 1 = не се заема/резервира, 2 = пропуска се при инвентаризация)
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO mst_item_status (item_status_id, item_status_name, rules, no_loan, skip_stock_take, input_date, last_update)
VALUES ('WO', 'Отчислен', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 1, 1, CURDATE(), CURDATE());
UPDATE mst_item_status SET item_status_name = 'В ремонт',    last_update = CURDATE() WHERE item_status_id = 'R'   AND item_status_name = 'Repair';
UPDATE mst_item_status SET item_status_name = 'Не се заема', last_update = CURDATE() WHERE item_status_id = 'NL'  AND item_status_name = 'No Loan';
UPDATE mst_item_status SET item_status_name = 'Липсващ', rules = 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', last_update = CURDATE()
 WHERE item_status_id = 'MIS' AND item_status_name = 'Missing';

-- ---------------------------------------------------------------------------
-- 8. Номенклатури на български (само редовете, които още са с английския текст)
-- ---------------------------------------------------------------------------
UPDATE mst_gmd SET gmd_name = 'Текст'                  WHERE gmd_code = 'TE'  AND gmd_name = 'Text';
UPDATE mst_gmd SET gmd_name = 'Произведение на изкуството' WHERE gmd_code = 'AR' AND gmd_name = 'Art Original';
UPDATE mst_gmd SET gmd_name = 'Таблица'                WHERE gmd_code = 'CH'  AND gmd_name = 'Chart';
UPDATE mst_gmd SET gmd_name = 'Компютърна програма'    WHERE gmd_code = 'CO'  AND gmd_name = 'Computer Software';
UPDATE mst_gmd SET gmd_name = 'Диорама'                WHERE gmd_code = 'DI'  AND gmd_name = 'Diorama';
UPDATE mst_gmd SET gmd_name = 'Диафилм'                WHERE gmd_code = 'FI'  AND gmd_name = 'Filmstrip';
UPDATE mst_gmd SET gmd_name = 'Флаш карти'             WHERE gmd_code = 'FL'  AND gmd_name = 'Flash Card';
UPDATE mst_gmd SET gmd_name = 'Игра'                   WHERE gmd_code = 'GA'  AND gmd_name = 'Game';
UPDATE mst_gmd SET gmd_name = 'Глобус'                 WHERE gmd_code = 'GL'  AND gmd_name = 'Globe';
UPDATE mst_gmd SET gmd_name = 'Комплект'               WHERE gmd_code = 'KI'  AND gmd_name = 'Kit';
UPDATE mst_gmd SET gmd_name = 'Карта'                  WHERE gmd_code = 'MA'  AND gmd_name = 'Map';
UPDATE mst_gmd SET gmd_name = 'Микроформа'             WHERE gmd_code = 'MI'  AND gmd_name = 'Microform';
UPDATE mst_gmd SET gmd_name = 'Ръкопис'                WHERE gmd_code = 'MN'  AND gmd_name = 'Manuscript';
UPDATE mst_gmd SET gmd_name = 'Модел'                  WHERE gmd_code = 'MO'  AND gmd_name = 'Model';
UPDATE mst_gmd SET gmd_name = 'Филм'                   WHERE gmd_code = 'MP'  AND gmd_name = 'Motion Picture';
UPDATE mst_gmd SET gmd_name = 'Микроскопски препарат'  WHERE gmd_code = 'MS'  AND gmd_name = 'Microscope Slide';
UPDATE mst_gmd SET gmd_name = 'Ноти'                   WHERE gmd_code = 'MU'  AND gmd_name = 'Music';
UPDATE mst_gmd SET gmd_name = 'Изображение'            WHERE gmd_code = 'PI'  AND gmd_name = 'Picture';
UPDATE mst_gmd SET gmd_name = 'Реалия'                 WHERE gmd_code = 'RE'  AND gmd_name = 'Realia';
UPDATE mst_gmd SET gmd_name = 'Диапозитив'             WHERE gmd_code = 'SL'  AND gmd_name = 'Slide';
UPDATE mst_gmd SET gmd_name = 'Звукозапис'             WHERE gmd_code = 'SO'  AND gmd_name = 'Sound Recording';
UPDATE mst_gmd SET gmd_name = 'Технически чертеж'      WHERE gmd_code = 'TD'  AND gmd_name = 'Technical Drawing';
UPDATE mst_gmd SET gmd_name = 'Прозрачно фолио'        WHERE gmd_code = 'TR'  AND gmd_name = 'Transparency';
UPDATE mst_gmd SET gmd_name = 'Видеозапис'             WHERE gmd_code = 'VI'  AND gmd_name = 'Video Recording';
UPDATE mst_gmd SET gmd_name = 'Оборудване'             WHERE gmd_code = 'EQ'  AND gmd_name = 'Equipment';
UPDATE mst_gmd SET gmd_name = 'Компютърен файл'        WHERE gmd_code = 'CF'  AND gmd_name = 'Computer File';
UPDATE mst_gmd SET gmd_name = 'Картографски материал'  WHERE gmd_code = 'CA'  AND gmd_name = 'Cartographic Material';
UPDATE mst_gmd SET gmd_name = 'CD-ROM'                 WHERE gmd_code = 'CD'  AND gmd_name = 'CD-ROM';
UPDATE mst_gmd SET gmd_name = 'Мултимедия'             WHERE gmd_code = 'MV'  AND gmd_name = 'Multimedia';
UPDATE mst_gmd SET gmd_name = 'Електронен ресурс'      WHERE gmd_code = 'ER'  AND gmd_name = 'Electronic Resource';
UPDATE mst_gmd SET gmd_name = 'DVD'                    WHERE gmd_code = 'DVD' AND gmd_name = 'Digital Versatile Disc';

UPDATE mst_frequency SET frequency = 'Седмично',         last_update = CURDATE() WHERE frequency_id = 1 AND frequency = 'Weekly';
UPDATE mst_frequency SET frequency = 'На две седмици',   last_update = CURDATE() WHERE frequency_id = 2 AND frequency = 'Bi-weekly';
UPDATE mst_frequency SET frequency = 'На 14 дни',        last_update = CURDATE() WHERE frequency_id = 3 AND frequency = 'Fourth-Nightly';
UPDATE mst_frequency SET frequency = 'Месечно',          last_update = CURDATE() WHERE frequency_id = 4 AND frequency = 'Monthly';
UPDATE mst_frequency SET frequency = 'Двумесечно',       last_update = CURDATE() WHERE frequency_id = 5 AND frequency = 'Bi-Monthly';
UPDATE mst_frequency SET frequency = 'Тримесечно',       last_update = CURDATE() WHERE frequency_id = 6 AND frequency = 'Quarterly';
UPDATE mst_frequency SET frequency = 'Три пъти годишно', last_update = CURDATE() WHERE frequency_id = 7 AND frequency = '3 Times a Year';
UPDATE mst_frequency SET frequency = 'Годишно',          last_update = CURDATE() WHERE frequency_id = 8 AND frequency = 'Annualy';

UPDATE mst_language SET language_name = 'Английски',    last_update = CURDATE() WHERE language_id = 'en' AND language_name = 'English';
UPDATE mst_language SET language_name = 'Индонезийски', last_update = CURDATE() WHERE language_id = 'id' AND language_name = 'Indonesia';
UPDATE mst_location SET location_name = 'Моята библиотека', last_update = CURDATE() WHERE location_id = 'SL' AND location_name = 'My Library';

-- ---------------------------------------------------------------------------
-- 9. Настройки
-- ---------------------------------------------------------------------------
-- Последният номер от хартиената инвентарна книга (0 = номерата започват от 1).
-- Номерата до него са за ретроспективно въвеждане на старите книги.
INSERT IGNORE INTO setting (setting_name, setting_value) VALUES ('bg_inventory_floor', 'i:0;');

-- Префикси на отделите в сигнатурата (локална таблица, както в COBISS – COMARC/H 99X d, елемент l)
SET @v = 'DO=Д;CH=Ч;KR=Кр';
INSERT IGNORE INTO setting (setting_name, setting_value) VALUES ('bg_location_prefix', CONCAT('s:', LENGTH(@v), ':"', @v, '";'));

-- Търсене за читателите: автоматично отрязване на думите (истори → история, истории)
INSERT IGNORE INTO setting (setting_name, setting_value) VALUES ('bg_search_auto_truncate', 'b:1;');

-- Търсачка за читателите с поддръжка на кирилица и И / ИЛИ / И НЕ
SET @v = 'SLiMS\\SearchEngine\\BgCatalogEngine';
INSERT INTO setting (setting_name, setting_value) VALUES ('search_engine', CONCAT('s:', LENGTH(@v), ':"', @v, '";'))
  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
