<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Bannerss"
 * "Last-Translator: "
 * "Language-Team: ZH"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_ZH_BANNER_NAME', "标语");
define('_ZH_BANNER_DESCRIPTION', "标语管理.");

/* ACLs */
define('_ZH_BANNER_ACL_DEFAULT', "使用标语");
define('_ZH_BANNER_ACL_MANAGEBANNERS', "标语管理");
define('_ZH_BANNER_ACL_MANAGEGROUPS', "分组管理");
define('_ZH_BANNER_ACL_BANNERSGROUPING', "标语分组");
define('_ZH_BANNER_ACL_UPDATEPROPERTIES', "更新组件");
define('_ZH_BANNER_ACL_VIEWREPORTS', "查看报告");

/* Layout Strings */
define('_ZH_BANNER_ACTION_DISPLAY_NAME', "标语");
define('_ZH_BANNER_ACTION_DISPLAY_DESCRIPTION', "显示标语");

/* Banners Management */
define('_ZH_BANNER_BANNERS_BANNERS', "标语");
define('_ZH_BANNER_BANNERS_BANNERID', "标语 ID");
define('_ZH_BANNER_BANNERS_TITLE', "标题");
define('_ZH_BANNER_BANNERS_URL', "URL");
define('_ZH_BANNER_BANNERS_BANNERTYPE', "类型");
define('_ZH_BANNER_BANNERS_BANNERTYPE_AUTODETECT', "自动检测");
define('_ZH_BANNER_BANNERS_BANNERTYPE_TEXT', "文本");
define('_ZH_BANNER_BANNERS_BANNERTYPE_IMAGE', "图片");
define('_ZH_BANNER_BANNERS_BANNERTYPE_FLASH', "Flash");
define('_ZH_BANNER_BANNERS_DIRECTION', "说明");
define('_ZH_BANNER_BANNERS_DIRECTION_BOTH', "全部");
define('_ZH_BANNER_BANNERS_DIRECTION_HORIZONTAL', "水平的");
define('_ZH_BANNER_BANNERS_DIRECTION_VERTICAL', "垂直的");
define('_ZH_BANNER_BANNERS_THROUGH_UPLOADING', "通过上传");
define('_ZH_BANNER_BANNERS_BANNER', "标语");
define('_ZH_BANNER_BANNERS_VIEWS', "查看");
define('_ZH_BANNER_BANNERS_VIEWS_LIMITATION', "查看限制");
define('_ZH_BANNER_BANNERS_CLICKS', "点击");
define('_ZH_BANNER_BANNERS_CLICKS_LIMITATION', "点击限制");
define('_ZH_BANNER_BANNERS_START_TIME', "开始时间");
define('_ZH_BANNER_BANNERS_STOP_TIME', "停止时间");
define('_ZH_BANNER_BANNERS_CREATE_TIME', "创建时间");
define('_ZH_BANNER_BANNERS_UPDATE_TIME', "更新时间");
define('_ZH_BANNER_BANNERS_RANDOM', "随即查看");
define('_ZH_BANNER_BANNERS_VISIBLE', "显示位置");
define('_ZH_BANNER_BANNERS_ADD', "添加标语");
define('_ZH_BANNER_BANNERS_UPDATE', "更新标语");
define('_ZH_BANNER_BANNERS_DELETE', "删除标语");
define('_ZH_BANNER_BANNERS_ADD_GROUPS', "添加分组");
define('_ZH_BANNER_BANNERS_NO_SELECTION', "从左边选择一个标语");
define('_ZH_BANNER_BANNERS_INCOMPLETE_FIELDS', "一些位置还没有（正确地）填写.");
define('_ZH_BANNER_BANNERS_ALREADY_EXISTS', "这个标题正在被({0})使用.");
define('_ZH_BANNER_BANNERS_CONFIRM_DELETE', "删除这个标语?");
define('_ZH_BANNER_BANNERS_MARK_GROUPS', "选择一个你想要添加标语的分组");

/* Banners Management Responses */
define('_ZH_BANNER_BANNERS_CREATED', "标语 {0} 被创建.");
define('_ZH_BANNER_BANNERS_UPDATED', "标语 {0} 被更新.");
define('_ZH_BANNER_BANNERS_DELETED', "标语 {0} 被删除.");

/* Banners Management Errors */
define('_ZH_BANNER_BANNERS_NOT_CREATED', "创建标语{0}出现问题.");
define('_ZH_BANNER_BANNERS_NOT_UPDATED', "更新标语{0}出现问题");
define('_ZH_BANNER_BANNERS_CANT_DELETE', "删除标语{0}出现问题");
define('_ZH_BANNER_BANNERS_ERROR_DOES_NOT_EXISTS', "标语不存在.");
define('_ZH_BANNER_BANNERS_ERROR_TITLE_DUPLICATE', "标语标题已经存在.");
define('_ZH_BANNER_ERROR_CANT_UPLOAD', "标语不能够被更新{0}.");
define('_ZH_BANNER_ERROR_BANNER_BAD_FORMAT', "不正确的格式.");
define('_ZH_BANNER_ERROR_CANT_DELETE_OLD', "不能删除老的标语文件.");

/* Banners Group Management */
define('_ZH_BANNER_GROUPS_GROUPS', "分组");
define('_ZH_BANNER_GROUPS_GROUPID', "分组 ID");
define('_ZH_BANNER_GROUPS_TITLE', "标题");
define('_ZH_BANNER_GROUPS_VISIBLE', "显示位置");
define('_ZH_BANNER_GROUPS_ADD', "增加分组");
define('_ZH_BANNER_GROUPS_DELETE', "删除分组");
define('_ZH_BANNER_GROUPS_ADD_BANNERS', "增加标语");
define('_ZH_BANNER_GROUPS_ADD_BANNER', "为分组添加标语");
define('_ZH_BANNER_GROUPS_NO_SELECTION', "清从左边选择一个分组");
define('_ZH_BANNER_GROUPS_INCOMPLETE_FIELDS', "一些内容没有被填写.");
define('_ZH_BANNER_GROUPS_CONFIRM_DELETE', "你确定要删除这个分组?");
define('_ZH_BANNER_GROUPS_MARK_BANNERS', "选择你要添加的标语");

/* Banners Group Management Responses*/
define('_ZH_BANNER_GROUPS_CREATED', "分组 {0}被创建.");
define('_ZH_BANNER_GROUPS_UPDATED', "分组 {0}被更新");
define('_ZH_BANNER_GROUPS_DELETED', "分组 {0}被删除.");
define('_ZH_BANNER_GROUPS_UPDATED_BANNERS', "标语和分组的关系被更新");

/* Banners Group Management Errors*/
define('_ZH_BANNER_GROUPS_NOT_CREATED', "创建分组{0}出现问题.");
define('_ZH_BANNER_GROUPS_NOT_UPDATED', "更新分组{0}出现问题.");
define('_ZH_BANNER_GROUPS_CANT_DELETE', "删除分组{0}出现问题.");
define('_ZH_BANNER_GROUPS_ERROR_DOES_NOT_EXISTS', "分组不存在.");
define('_ZH_BANNER_GROUPS_ERROR_TITLE_DUPLICATE', "分组标题已经存在.");

/* Banners Properties */
define('_ZH_BANNER_PROPERTIES_PROPERTIES', "组件");
define('_ZH_BANNER_PROPERTIES_GROUP', "分组");
define('_ZH_BANNER_PROPERTIES_COUNT', "计数");
define('_ZH_BANNER_PROPERTIES_LAYOUT_HEADER', "头部");
define('_ZH_BANNER_PROPERTIES_LAYOUT_BAR1', "左边工具条");
define('_ZH_BANNER_PROPERTIES_LAYOUT_MAIN', "主要部分");
define('_ZH_BANNER_PROPERTIES_LAYOUT_BAR2', "有不工具条");
define('_ZH_BANNER_PROPERTIES_LAYOUT_FOOTER', "底部");
define('_ZH_BANNER_PROPERTIES_NO_GROUPING', "没有分组");

/* Banners Properties Responses*/
define('_ZH_BANNER_PROPERTIES_UPDATED', "组件被更新.");

/* Banners Properties Errors*/
define('_ZH_BANNER_PROPERTIES_NOT_UPDATED', "更新组件出现问题.");

/* Banners Reports */
define('_ZH_BANNER_REPORTS_REPORTS', "报告");
define('_ZH_BANNER_REPORTS_BANNERS_TITLE', "标题");
define('_ZH_BANNER_REPORTS_BANNERS_VIEWS', "查看");
define('_ZH_BANNER_REPORTS_BANNERS_CLICKS', "点击");
define('_ZH_BANNER_REPORTS_BANNERS_TIME', "停止时间");
define('_ZH_BANNER_REPORTS_BANNERS_STATUS', "状态");
define('_ZH_BANNER_REPORTS_BANNERS_STATUS_ALWAYS', "永远");
define('_ZH_BANNER_REPORTS_BANNERS_STATUS_RANDOM', "随即");
define('_ZH_BANNER_REPORTS_BANNERS_STATUS_INVISIBLE', "不可见");
define('_ZH_BANNER_REPORTS_BANNERS_STATUS_VISIBLE', "可见");

