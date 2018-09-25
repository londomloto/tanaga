/*
 Navicat Premium Data Transfer

 Source Server         : mysql@pusdikadm.xyz
 Source Server Type    : MySQL
 Source Server Version : 50561
 Source Host           : 156.67.221.18:3306
 Source Schema         : tanaga

 Target Server Type    : MySQL
 Target Server Version : 50561
 File Encoding         : 65001

 Date: 24/09/2018 20:54:39
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bpm_diagrams
-- ----------------------------
DROP TABLE IF EXISTS `bpm_diagrams`;
CREATE TABLE `bpm_diagrams`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'activity',
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `description` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `cover` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `created_date` datetime NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 60 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of bpm_diagrams
-- ----------------------------
INSERT INTO `bpm_diagrams` VALUES (57, 'Graph.diagram.type.Activity', 'Classic Workflow', 'classic-workflow', 'Standard workflow process', '88ef36459f8e70693a77561c112c6a4c.jpg', '2017-12-05 09:39:42', NULL);
INSERT INTO `bpm_diagrams` VALUES (58, 'Graph.diagram.type.Activity', 'Simple Workflow', 'simple-workflow', 'Only todo and done task flow', '1b77d194ced034831f3da493d61cf16d.jpg', '2017-12-21 18:28:18', NULL);
INSERT INTO `bpm_diagrams` VALUES (59, 'Graph.diagram.type.Activity', 'Backlog Workflow', 'backlog-workflow', 'Task flow follow backlog activity', '1560c1c2fc834c43f0b83699e9683273.jpg', '2017-12-21 18:31:50', NULL);

-- ----------------------------
-- Table structure for bpm_forms
-- ----------------------------
DROP TABLE IF EXISTS `bpm_forms`;
CREATE TABLE `bpm_forms`  (
  `bf_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bf_activity` bigint(20) NULL DEFAULT NULL,
  `bf_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `bf_description` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `bf_tpl_file` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `bf_tpl_orig` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`bf_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 39 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of bpm_forms
-- ----------------------------
INSERT INTO `bpm_forms` VALUES (27, 860, 'Form Start', 'No description', '0217ac43eeb0d07bde0a5db69540dab0.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (28, 861, 'Form Todo', 'No description', 'b518cb7a6dc5bb53dcc7a10aee7c2e79.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (29, 862, 'Form Doing', 'No description', 'f93d3cce7efb21ee2667d46f0249fb2d.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (30, 863, 'Form Done', 'No description', '4b9faff43018b7fbb9b1645917035430.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (31, 869, 'Task Editor', 'No description', '11342055cc8a7c59f879c3192f35f614.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (32, 871, 'Task Editor', 'No description', 'dfc6bab2068a87382e5e5bca931f6a2f.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (33, 870, 'Task Editor', 'No description', '6b0d21713aa2bef4a4598df191880d1c.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (34, 872, 'Task Editor', 'No description', '1661b1e82c1f7daeb026c195099ca4c9.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (35, 873, 'Task Editor', 'No description', 'a5d965a5d94d1368b0254e6f8d489e4a.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (36, 866, 'Task Editor', 'No description', '7c42debef080e0c31317b68fbba6e219.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (37, 865, 'Task Editor', 'No description', '61cccdd90be2dbf38b439a5f0ef82c13.html', 'task-editor.html');
INSERT INTO `bpm_forms` VALUES (38, 867, 'Task Editor', 'No description', '2938dd470cd9f698343dec57f8029955.html', 'task-editor.html');

-- ----------------------------
-- Table structure for bpm_forms_roles
-- ----------------------------
DROP TABLE IF EXISTS `bpm_forms_roles`;
CREATE TABLE `bpm_forms_roles`  (
  `bfr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bfr_bf_id` bigint(20) NULL DEFAULT NULL,
  `bfr_sr_id` bigint(20) NULL DEFAULT NULL,
  PRIMARY KEY (`bfr_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of bpm_forms_roles
-- ----------------------------
INSERT INTO `bpm_forms_roles` VALUES (1, 27, 1);
INSERT INTO `bpm_forms_roles` VALUES (2, 28, 1);
INSERT INTO `bpm_forms_roles` VALUES (3, 29, 1);
INSERT INTO `bpm_forms_roles` VALUES (4, 30, 1);
INSERT INTO `bpm_forms_roles` VALUES (5, 31, 1);
INSERT INTO `bpm_forms_roles` VALUES (6, 32, 1);
INSERT INTO `bpm_forms_roles` VALUES (7, 33, 1);
INSERT INTO `bpm_forms_roles` VALUES (8, 34, 1);
INSERT INTO `bpm_forms_roles` VALUES (9, 35, 1);
INSERT INTO `bpm_forms_roles` VALUES (10, 36, 1);
INSERT INTO `bpm_forms_roles` VALUES (11, 37, 1);
INSERT INTO `bpm_forms_roles` VALUES (12, 38, 1);

-- ----------------------------
-- Table structure for bpm_forms_users
-- ----------------------------
DROP TABLE IF EXISTS `bpm_forms_users`;
CREATE TABLE `bpm_forms_users`  (
  `bfu_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bfu_bf_id` bigint(20) NULL DEFAULT NULL,
  `bfu_su_id` bigint(20) NULL DEFAULT NULL,
  PRIMARY KEY (`bfu_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Compact;
