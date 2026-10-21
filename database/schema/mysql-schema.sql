/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `bagian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bagian` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_bagian` varchar(255) NOT NULL,
  `kode_bagian` varchar(255) NOT NULL,
  `kepala_bagian_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bagian_kode_bagian_unique` (`kode_bagian`),
  KEY `bagian_kepala_bagian_id_foreign` (`kepala_bagian_id`),
  CONSTRAINT `bagian_kepala_bagian_id_foreign` FOREIGN KEY (`kepala_bagian_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bagian_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bagian_user` (
  `bagian_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `peran_tim` varchar(20) NOT NULL DEFAULT 'anggota',
  PRIMARY KEY (`bagian_id`,`user_id`),
  KEY `bagian_user_user_id_foreign` (`user_id`),
  CONSTRAINT `bagian_user_bagian_id_foreign` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bagian_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kamus_kerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kamus_kerja` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `input` text DEFAULT NULL,
  `proses` text DEFAULT NULL,
  `output` text DEFAULT NULL,
  `bagian_id` bigint(20) unsigned DEFAULT NULL,
  `dibuat_oleh` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kamus_kerja_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `idx_kamus_tim_nama` (`bagian_id`,`nama`),
  CONSTRAINT `kamus_kerja_bagian_id_foreign` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kamus_kerja_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `laporan_harian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laporan_harian` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tugas_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `uraian` text NOT NULL,
  `output` text DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL DEFAULT 'kantor',
  `file_lampiran` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `disetujui_oleh` bigint(20) unsigned DEFAULT NULL,
  `disetujui_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `laporan_harian_user_id_foreign` (`user_id`),
  KEY `laporan_harian_tugas_id_foreign` (`tugas_id`),
  KEY `laporan_harian_disetujui_oleh_foreign` (`disetujui_oleh`),
  CONSTRAINT `laporan_harian_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `laporan_harian_tugas_id_foreign` FOREIGN KEY (`tugas_id`) REFERENCES `tugas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `laporan_harian_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_approval` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `laporan_id` bigint(20) unsigned NOT NULL,
  `approver_id` bigint(20) unsigned NOT NULL,
  `aksi` varchar(255) NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_approval_laporan_id_foreign` (`laporan_id`),
  KEY `log_approval_approver_id_foreign` (`approver_id`),
  CONSTRAINT `log_approval_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `log_approval_laporan_id_foreign` FOREIGN KEY (`laporan_id`) REFERENCES `laporan_harian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sop` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `versi` varchar(255) NOT NULL DEFAULT '1.0',
  `keterangan` text DEFAULT NULL,
  `dibuat_oleh` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bagian_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sop_kode_unique` (`kode`),
  KEY `sop_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `sop_bagian_id_foreign` (`bagian_id`),
  CONSTRAINT `sop_bagian_id_foreign` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sop_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sop_langkah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sop_langkah` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sop_id` bigint(20) unsigned NOT NULL,
  `urutan` int(10) unsigned NOT NULL,
  `input` text DEFAULT NULL,
  `proses` text NOT NULL,
  `output` text DEFAULT NULL,
  `sumber_input` varchar(255) NOT NULL DEFAULT 'sebelumnya',
  `durasi_hari` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sop_langkah_sop_id_urutan_unique` (`sop_id`,`urutan`),
  CONSTRAINT `sop_langkah_sop_id_foreign` FOREIGN KEY (`sop_id`) REFERENCES `sop` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sop_pelaksanaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sop_pelaksanaan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sop_id` bigint(20) unsigned NOT NULL,
  `dibuat_oleh` bigint(20) unsigned NOT NULL,
  `nama` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tugas_induk_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sop_pelaksanaan_sop_id_foreign` (`sop_id`),
  KEY `sop_pelaksanaan_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `sop_pelaksanaan_tugas_induk_id_foreign` (`tugas_induk_id`),
  CONSTRAINT `sop_pelaksanaan_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`),
  CONSTRAINT `sop_pelaksanaan_sop_id_foreign` FOREIGN KEY (`sop_id`) REFERENCES `sop` (`id`),
  CONSTRAINT `sop_pelaksanaan_tugas_induk_id_foreign` FOREIGN KEY (`tugas_induk_id`) REFERENCES `tugas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tugas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dibuat_oleh` bigint(20) unsigned NOT NULL,
  `ditugaskan_ke` bigint(20) unsigned NOT NULL,
  `bagian_id` bigint(20) unsigned NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tenggat` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'belum_dikerjakan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `induk_tugas_id` bigint(20) unsigned DEFAULT NULL,
  `input_tugas_id` bigint(20) unsigned DEFAULT NULL,
  `sop_pelaksanaan_id` bigint(20) unsigned DEFAULT NULL,
  `sop_langkah_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `jenis_input` varchar(255) NOT NULL DEFAULT 'manual',
  `uraian_input` text DEFAULT NULL,
  `nomor_surat` varchar(255) DEFAULT NULL,
  `asal_surat` varchar(255) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `file_input` varchar(255) DEFAULT NULL,
  `output_rencana` text DEFAULT NULL,
  `output_realisasi` text DEFAULT NULL,
  `file_output` varchar(255) DEFAULT NULL,
  `kamus_kerja_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sop_langkah_pelaksana` (`sop_pelaksanaan_id`,`sop_langkah_id`,`ditugaskan_ke`),
  KEY `tugas_dibuat_oleh_foreign` (`dibuat_oleh`),
  KEY `tugas_ditugaskan_ke_foreign` (`ditugaskan_ke`),
  KEY `tugas_bagian_id_foreign` (`bagian_id`),
  KEY `tugas_induk_tugas_id_foreign` (`induk_tugas_id`),
  KEY `tugas_input_tugas_id_foreign` (`input_tugas_id`),
  KEY `tugas_sop_langkah_id_foreign` (`sop_langkah_id`),
  KEY `tugas_kamus_kerja_id_foreign` (`kamus_kerja_id`),
  CONSTRAINT `tugas_bagian_id_foreign` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tugas_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tugas_ditugaskan_ke_foreign` FOREIGN KEY (`ditugaskan_ke`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tugas_induk_tugas_id_foreign` FOREIGN KEY (`induk_tugas_id`) REFERENCES `tugas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tugas_input_tugas_id_foreign` FOREIGN KEY (`input_tugas_id`) REFERENCES `tugas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tugas_kamus_kerja_id_foreign` FOREIGN KEY (`kamus_kerja_id`) REFERENCES `kamus_kerja` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tugas_sop_langkah_id_foreign` FOREIGN KEY (`sop_langkah_id`) REFERENCES `sop_langkah` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tugas_sop_pelaksanaan_id_foreign` FOREIGN KEY (`sop_pelaksanaan_id`) REFERENCES `sop_pelaksanaan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'staf',
  `bagian_id` bigint(20) unsigned DEFAULT NULL,
  `atasan_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nip_unique` (`nip`),
  KEY `users_bagian_id_foreign` (`bagian_id`),
  KEY `users_atasan_id_foreign` (`atasan_id`),
  CONSTRAINT `users_atasan_id_foreign` FOREIGN KEY (`atasan_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_bagian_id_foreign` FOREIGN KEY (`bagian_id`) REFERENCES `bagian` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_08_14_010000_create_bagian_and_update_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_08_14_011207_create_tugas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_08_14_011410_create_laporan_harian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_08_14_011615_create_log_approval_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_10_21_000001_add_alur_pekerjaan_and_teams',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_10_21_000002_add_peran_tim_to_bagian_user',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_10_21_000003_add_induk_delegasi_sop',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_10_21_000004_allow_multiple_assignees_per_sop_step',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_10_21_000005_scope_sop_by_team',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_10_21_000006_create_kamus_kerja',7);
