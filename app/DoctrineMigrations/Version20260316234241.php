<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316234241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dtb_base_info ADD COLUMN option_guest_purchase BOOLEAN DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__dtb_base_info AS SELECT id, country_id, pref_id, company_name, company_kana, postal_code, addr01, addr02, phone_number, business_hour, email01, email02, email03, email04, shop_name, shop_kana, shop_name_eng, update_date, good_traded, message, delivery_free_amount, delivery_free_quantity, option_mypage_order_status_display, option_nostock_hidden, option_favorite_product, option_product_delivery_fee, invoice_registration_number, option_product_tax_rule, option_customer_activate, option_remember_me, option_mail_notifier, authentication_key, php_path, option_point, basic_point_rate, point_conversion_rate, ga_id, discriminator_type FROM dtb_base_info');
        $this->addSql('DROP TABLE dtb_base_info');
        $this->addSql('CREATE TABLE dtb_base_info (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, country_id SMALLINT UNSIGNED DEFAULT NULL, pref_id SMALLINT UNSIGNED DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, company_kana VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(8) DEFAULT NULL, addr01 VARCHAR(255) DEFAULT NULL, addr02 VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(14) DEFAULT NULL, business_hour VARCHAR(255) DEFAULT NULL, email01 VARCHAR(255) DEFAULT NULL, email02 VARCHAR(255) DEFAULT NULL, email03 VARCHAR(255) DEFAULT NULL, email04 VARCHAR(255) DEFAULT NULL, shop_name VARCHAR(255) DEFAULT NULL, shop_kana VARCHAR(255) DEFAULT NULL, shop_name_eng VARCHAR(255) DEFAULT NULL, update_date DATETIME NOT NULL --(DC2Type:datetimetz)
        , good_traded VARCHAR(4000) DEFAULT NULL, message VARCHAR(4000) DEFAULT NULL, delivery_free_amount NUMERIC(12, 2) DEFAULT NULL, delivery_free_quantity INTEGER UNSIGNED DEFAULT NULL, option_mypage_order_status_display BOOLEAN DEFAULT 1 NOT NULL, option_nostock_hidden BOOLEAN DEFAULT 0 NOT NULL, option_favorite_product BOOLEAN DEFAULT 1 NOT NULL, option_product_delivery_fee BOOLEAN DEFAULT 0 NOT NULL, invoice_registration_number VARCHAR(255) DEFAULT NULL, option_product_tax_rule BOOLEAN DEFAULT 0 NOT NULL, option_customer_activate BOOLEAN DEFAULT 1 NOT NULL, option_remember_me BOOLEAN DEFAULT 1 NOT NULL, option_mail_notifier BOOLEAN DEFAULT 0 NOT NULL, authentication_key VARCHAR(255) DEFAULT NULL, php_path VARCHAR(255) DEFAULT NULL, option_point BOOLEAN DEFAULT 1 NOT NULL, basic_point_rate NUMERIC(10, 0) DEFAULT \'1\', point_conversion_rate NUMERIC(10, 0) DEFAULT \'1\', ga_id VARCHAR(255) DEFAULT NULL, discriminator_type VARCHAR(255) NOT NULL, CONSTRAINT FK_1D3655F4F92F3E70 FOREIGN KEY (country_id) REFERENCES mtb_country (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1D3655F4E171EF5F FOREIGN KEY (pref_id) REFERENCES mtb_pref (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO dtb_base_info (id, country_id, pref_id, company_name, company_kana, postal_code, addr01, addr02, phone_number, business_hour, email01, email02, email03, email04, shop_name, shop_kana, shop_name_eng, update_date, good_traded, message, delivery_free_amount, delivery_free_quantity, option_mypage_order_status_display, option_nostock_hidden, option_favorite_product, option_product_delivery_fee, invoice_registration_number, option_product_tax_rule, option_customer_activate, option_remember_me, option_mail_notifier, authentication_key, php_path, option_point, basic_point_rate, point_conversion_rate, ga_id, discriminator_type) SELECT id, country_id, pref_id, company_name, company_kana, postal_code, addr01, addr02, phone_number, business_hour, email01, email02, email03, email04, shop_name, shop_kana, shop_name_eng, update_date, good_traded, message, delivery_free_amount, delivery_free_quantity, option_mypage_order_status_display, option_nostock_hidden, option_favorite_product, option_product_delivery_fee, invoice_registration_number, option_product_tax_rule, option_customer_activate, option_remember_me, option_mail_notifier, authentication_key, php_path, option_point, basic_point_rate, point_conversion_rate, ga_id, discriminator_type FROM __temp__dtb_base_info');
        $this->addSql('DROP TABLE __temp__dtb_base_info');
        $this->addSql('CREATE INDEX IDX_1D3655F4F92F3E70 ON dtb_base_info (country_id)');
        $this->addSql('CREATE INDEX IDX_1D3655F4E171EF5F ON dtb_base_info (pref_id)');
    }
}
