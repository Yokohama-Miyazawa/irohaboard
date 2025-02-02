;<?php exit(); ?>
;/**
; * ACL Configuration
; *
; * @link          http://cakephp.org CakePHP(tm) Project
; * @package       app.Config
; * @since         CakePHP(tm) v 0.10.0.1076
; */

; acl.ini.php - CakePHP ACL Configuration
; ---------------------------------------------------------------------
; Use this file to specify user permissions.
; aco = access control object (something in your application)
; aro = access request object (something requesting access)
;
; User records are added as follows:
;
; [uid]
; groups = group1, group2, group3
; allow = aco1, aco2, aco3
; deny = aco4, aco5, aco6
;
; Group records are added in a similar manner:
;
; [gid]
; allow = aco1, aco2, aco3
; deny = aco4, aco5, aco6
;
; The allow, deny, and groups sections are all optional.
; NOTE: groups names *cannot* ever be the same as usernames!
;
; ACL permissions are checked in the following order:
; 1. Check for user denies (and DENY if specified)
; 2. Check for user allows (and ALLOW if specified)
; 3. Gather user's groups
; 4. Check group denies (and DENY if specified)
; 5. Check group allows (and ALLOW if specified)
; 6. If no aro, aco, or group information is found, DENY
;
; ---------------------------------------------------------------------

;-------------------------------------
;Users
;-------------------------------------

[username-goes-here]
groups = group1, group2
deny = aco1, aco2
allow = aco3, aco4

;-------------------------------------
;Groups
;-------------------------------------

[groupname-goes-here]
deny = aco5, aco6
allow = aco7, aco8
