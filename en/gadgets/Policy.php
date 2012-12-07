<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Policy"
 * "Last-Translator: Amir Mohammad Saied <amir@gluegadget.com>, Ali Fazelzadeh <afz@php.net>"
 * "Language-Team: EN"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_EN_POLICY_NAME', "Policy");
define('_EN_POLICY_DESCRIPTION', "Manage what's happening around your web site");

/* ACLs */
define('_EN_POLICY_ACL_MANAGEPOLICY', "Policy management");
define('_EN_POLICY_ACL_IPBLOCKING', "IP blocking");
define('_EN_POLICY_ACL_MANAGEIPS', "Add/Edit/Delete IPs");
define('_EN_POLICY_ACL_AGENTBLOCKING', "Agent blocking");
define('_EN_POLICY_ACL_MANAGEAGENTS', "Add/Edit/Delete agents");
define('_EN_POLICY_ACL_ENCRYPTION', "Encryption management");
define('_EN_POLICY_ACL_MANAGEENCRYPTIONKEY', "Encryption keys management");
define('_EN_POLICY_ACL_ANTISPAM', "AntiSpam management");
define('_EN_POLICY_ACL_ADVANCEDPOLICIES', "Advanced settings management");

/* common */
define('_EN_POLICY_BLOCKED', "Blocked");

/* IP Range Blocking */
define('_EN_POLICY_IP_BLOCKING', "IP Blocking");
define('_EN_POLICY_IP_ADDRESS', "IP Address");
define('_EN_POLICY_IP_RANGE', "IP Range");
define('_EN_POLICY_IP_BLOCK_UNDEFINED', "Block undefined IP");

/* Agent Blocking */
define('_EN_POLICY_AGENT_BLOCKING', "Agent Blocking");
define('_EN_POLICY_AGENT', "Agent");
define('_EN_POLICY_AGENT_BLOCK_UNDEFINED', "Block undefined agent");

/* Encryption */
define('_EN_POLICY_ENCRYPTION', "Encryption");
define('_EN_POLICY_ENCRYPTION_KEY_AGE', "Key Age");
define('_EN_POLICY_ENCRYPTION_KEY_LEN', "Key Length");
define('_EN_POLICY_ENCRYPTION_64BIT',   "64 bit");
define('_EN_POLICY_ENCRYPTION_128BIT',  "128 bit");
define('_EN_POLICY_ENCRYPTION_256BIT',  "256 bit");
define('_EN_POLICY_ENCRYPTION_512BIT',  "512 bit");
define('_EN_POLICY_ENCRYPTION_1024BIT', "1024 bit");
define('_EN_POLICY_ENCRYPTION_KEY_START_DATE', "Key Start Date");

//AntiSpam
define('_EN_POLICY_ANTISPAM', "AntiSpam");
define('_EN_POLICY_ANTISPAM_ALLOWDUPLICATE', "Allow duplicate messages");
define('_EN_POLICY_ANTISPAM_CAPTCHA', "Captcha");
define('_EN_POLICY_ANTISPAM_CAPTCHA_ALWAYS', "Always");
define('_EN_POLICY_ANTISPAM_CAPTCHA_ANONYMOUS', "Anonymous users only");
define('_EN_POLICY_ANTISPAM_FILTER', "Spam filter");
define('_EN_POLICY_ANTISPAM_PROTECTEMAIL', "Protect emails");

//Math Captcha
define('_EN_POLICY_CAPTCHA_MATH_PLUS', "{0} plus {1} is equal to?");
define('_EN_POLICY_CAPTCHA_MATH_MINUS', "{0} minus {1} is equal to?");
define('_EN_POLICY_CAPTCHA_MATH_MULTIPLY', "{0} multiplied by {1} is equal to?");

//Advanced Settings
define('_EN_POLICY_ADVANCED_POLICIES', "Advanced Policies");
define('_EN_POLICY_PASSWD_COMPLEXITY', "Password complexity");
define('_EN_POLICY_PASSWD_BAD_COUNT', "Password invalid attemps");
define('_EN_POLICY_PASSWD_LOCKEDOUT_TIME', "Password lockedout time");
define('_EN_POLICY_PASSWD_MAX_AGE', "Password maximum age");
define('_EN_POLICY_PASSWD_RESISTANT', "Resistant");
define('_EN_POLICY_PASSWD_MIN_LEN', "Password minimum length");
define('_EN_POLICY_XSS_PARSING_LEVEL', "XSS parsing level");
define('_EN_POLICY_XSS_PARSING_NORMAL', "Normal");
define('_EN_POLICY_XSS_PARSING_PARANOID', "Paranoid");
define('_EN_POLICY_SESSION_IDLE_TIMEOUT', "Idle timeout");
define('_EN_POLICY_SESSION_REMEMBER_TIMEOUT', "Remember me timeout");

// Responses
define('_EN_POLICY_RESPONSE_IP_ADDED',              "IP range was successfully added");
define('_EN_POLICY_RESPONSE_IP_NOT_ADDED',          "Failed to add the IP range");
define('_EN_POLICY_RESPONSE_IP_EDITED',             "IP range was successfully updated");
define('_EN_POLICY_RESPONSE_IP_NOT_EDITED',         "Failed to update the IP range");
define('_EN_POLICY_RESPONSE_IP_DELETED',            "IP range was successfully deleted");
define('_EN_POLICY_RESPONSE_IP_NOT_DELETED',        "Failed to delete the IP range address");
define('_EN_POLICY_RESPONSE_CONFIRM_DELETE_IP',     "Are you sure you want to delete this IP range?");
define('_EN_POLICY_RESPONSE_AGENT_ADDED',           "Agent was successfully added");
define('_EN_POLICY_RESPONSE_AGENT_NOT_ADDEDD',      "Failed to add the Agent");
define('_EN_POLICY_RESPONSE_AGENT_EDITED',          "Agent was successfully updated");
define('_EN_POLICY_RESPONSE_AGENT_NOT_EDITED',      "Failed to update the Agent");
define('_EN_POLICY_RESPONSE_AGENT_DELETED',         "Agent was successfully deleted");
define('_EN_POLICY_RESPONSE_AGENT_NOT_DELETED',     "Failed to remove the agent");
define('_EN_POLICY_RESPONSE_CONFIRM_DELETE_AGENT',  "Are you sure you want to delete this Agent?");
define('_EN_POLICY_RESPONSE_ENCRYPTION_UPDATED',    "Encryption's settings was successfully updated");
define('_EN_POLICY_RESPONSE_ANTISPAM_UPDATED',      "AntiSpam's settings was successfully updated");
define('_EN_POLICY_RESPONSE_ADVANCED_POLICIES_UPDATED', "Advanced policies was successfully updated");
define('_EN_POLICY_RESPONSE_PROPERTIES_UPDATED',        "Policy's properties was successfully updated");
define('_EN_POLICY_RESPONSE_PROPERTIES_NOT_UPDATED',    "Failed to save policy's properties");
