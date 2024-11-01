=== SJ Reading Time ===
Contributors: janoyvassell
Tags: reading time, word count, words per minute, estimated time
Requires at least: 4.5
Tested up to: 6.5.4
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SJ Reading Time helps you to quickly estimate your content read time and insert using a shortcode. 

== Description ==

SJ Reading Time is a simple content read time estimator. Our settings allow you to control how many words per minute (wpm) on average you would like to use to determine read time, 
as well as, if images should be part of this estimate.

A shortcode is provided which gives complete control over where on your post you would like the estimate to be placed.

For example:
[sjrt_reading_time label="Reading time:" postfix="mins" postfix_singular="min"]

All the above attributes are optional and if not provided the default values will be shown which are:

* `label` - Reading time
* `postfix_singular` - min
* `postfix` - mins


== Screenshots ==

1. Example of read time being displayed at the top of a blog post.
2. Settings used to control read time estimate

== Changelog ==

= 1.0.1 =
* Test with upgraded WordPress version, add plugin uri *
= 1.0.0 =
* SJ Reading time initial release - estimate read time based on words per minute & images per post.
