.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.rst.txt


.. _admin-manual:

Administrator Manual
====================

Describes how to manage the extension from an administrator’s point of
view. That relates to Page/User TSconfig, permissions, configuration
etc., which administrator level users have access to.

Language should be non/semi-technical, explaining, using small
examples.

Target group: **Administrators**


Installation
------------

1. Install the extension via composer `composer require aoe/felogin-bruteforce-protection`, from TER or from `our GitHub repository <https://github.com/AOEpeople/felogin_bruteforce_protection>`_.

2. Configure this TYPO3-Extension (in TYPO3 Extension-Manager). See the "Screenshots" section as well.


How does it work?
-----------------

The identification value (IP or login name) is saved to database as a md5-hash or updated when login fails. Although
the time of the first and the last attempt and the number of attempts is saved.
If the user logs in successfully before he gets restricted the database entry will be deleted.

If the criteria for a restriction are reached the user is not able to login for the configured period of time.

FAQ
^^^

- How can I release a restriced user?

A manual release is only possible by deleting the corresponding database entry in `tx_feloginbruteforceprotection_domain_model_entry`.