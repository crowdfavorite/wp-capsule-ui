/* vim:ts=4:sts=4:sw=4:
 * ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Ajax.org Code Editor (ACE).
 *
 * The Initial Developer of the Original Code is
 * Ajax.org B.V.
 * Portions created by the Initial Developer are Copyright (C) 2010
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *      Fabian Jakobs <fabian AT ajax DOT org>
 *      Mihai Sucan <mihai DOT sucan AT gmail DOT com>
 *      Chris Spencer <chris.ag.spencer AT googlemail DOT com>
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 or later (the "GPL"), or
 * the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the MPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the MPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK ***** */define("cf/js/syntax/cfmarkdown_highlight_rules",["require","exports","module","ace/lib/oop","ace/mode/text_highlight_rules","ace/mode/javascript_highlight_rules","ace/mode/xml_highlight_rules","ace/mode/html_highlight_rules","ace/mode/css_highlight_rules","ace/mode/json_highlight_rules","ace/mode/sql_highlight_rules","cf/js/syntax/cf_php_highlight_rules"],function(e,t,n){"use strict";function p(e,t){return{token:"support.function",regex:"^```\\s*"+e+"\\s*$",next:t+"start"}}var r=e("ace/lib/oop"),i=e("ace/mode/text_highlight_rules").TextHighlightRules,s=e("ace/mode/javascript_highlight_rules").JavaScriptHighlightRules,o=e("ace/mode/xml_highlight_rules").XmlHighlightRules,u=e("ace/mode/html_highlight_rules").HtmlHighlightRules,a=e("ace/mode/css_highlight_rules").CssHighlightRules,f=e("cf/js/syntax/cf_php_highlight_rules").PhpHighlightRules,l=e("cf/js/syntax/cf_php_highlight_rules").PhpLangHighlightRules,c=e("ace/mode/sql_highlight_rules").SqlHighlightRules,h=e("ace/mode/json_highlight_rules").JsonHighlightRules,d=function(){this.$rules={start:[{token:"empty_line",regex:"^$"},{token:"keyword",regex:twttr.txt.regexSupplant("(?:^|\\s)#{atSigns}[a-zA-Z0-9._-]{1,20}")},{token:["constant","constant"],regex:twttr.txt.regexSupplant("(#{hashSigns})(#{hashtagAlphaNumeric}*#{hashtagAlpha}#{hashtagAlphaNumeric}*)")},{token:["support.function","support.function","support.function"],regex:"(`+)([^\\r]*?[^`])(\\1)"},{token:"support.function",regex:"^[ ]{4}.+"},{token:"markup.heading.1",regex:"^=+(?=\\s*$)"},{token:"markup.heading.1",regex:"^\\-+(?=\\s*$)"},{token:function(e){return"markup.heading."+(e.length-1)},regex:"^#{1,6} "},p("javascript","js-"),p("js","js-"),p("xml","xml-"),p("html","html-"),p("css","css-"),p("php","php-"),p("sql","sql-"),p("json","json-"),{token:"support.function",regex:"^```[a-zA-Z]+\\s*$",next:"githubblock"},{token:"string",regex:"^>[ ].+$",next:"blockquote"},{token:["text","constant","text","url","string","text"],regex:'^([ ]{0,3}\\[)([^\\]]+)(\\]:\\s*)([^ ]+)(\\s*(?:["][^"]+["])?(\\s*))$'},{token:["text","string","text","constant","text"],regex:"(\\[)((?:[[^\\]]*\\]|[^\\[\\]])*)(\\][ ]?(?:\\n[ ]*)?\\[)(.*?)(\\])"},{token:["text","string","text","markup.underline","string","text"],regex:'(\\[)(\\[[^\\]]*\\]|[^\\[\\]]*)(\\]\\([ \\t]*)(<?(?:(?:[^\\(]*?\\([^\\)]*?\\)\\S*?)|(?:.*?))>?)((?:[ 	]*"(?:.*?)"[ \\t]*)?)(\\))'},{token:"constant",regex:"^[ ]{0,2}(?:[ ]?\\*[ ]?){3,}\\s*$"},{token:"constant",regex:"^[ ]{0,2}(?:[ ]?\\-[ ]?){3,}\\s*$"},{token:"constant",regex:"^[ ]{0,2}(?:[ ]?\\_[ ]?){3,}\\s*$"},{token:"markup.list",regex:"^\\s{0,3}(?:[*+-]|\\d+\\.)\\s+",next:"listblock"},{token:["string","string","string"],regex:"([*]{2}|[_]{2}(?=\\S))([^\\r]*?\\S[*_]*)(\\1)"},{token:["string","string","string"],regex:"([*]|[_](?=\\S))([^\\r]*?\\S[*_]*)(\\1)"},{token:["text","url","text"],regex:"(<)((?:https?|ftp|dict):[^'\">\\s]+|(?:mailto:)?[-.\\w]+\\@[-a-z0-9]+(?:\\.[-a-z0-9]+)*\\.[a-z]+)(>)"},{token:"text",regex:"[^\\*_@%$`\\[#<>]+?"}],listblock:[{token:"empty_line",regex:"^$",next:"start"},{token:"markup.list",regex:".+"}],blockquote:[{token:"empty_line",regex:"^\\s*$",next:"start"},{token:"string",regex:".+"}],githubblock:[{token:"support.function",regex:"^```",next:"start"},{token:"support.function",regex:".+"}]};this.embedRules(s,"js-",[{token:"support.function",regex:"^```",next:"start"}]);this.embedRules(f,"html-",[{token:"support.function",regex:"^```",next:"start"}]);this.embedRules(a,"css-",[{token:"support.function",regex:"^```",next:"start"}]);this.embedRules(o,"xml-",[{token:"support.function",regex:"^```",next:"start"}]);this.embedRules(l,"php-",[{token:"support.function",regex:"^```",next:"start"}]);this.embedRules(c,"sql-",[{token:"support.function",regex:"^```",next:"start"}]);this.embedRules(c,"json-",[{token:"support.function",regex:"^```",next:"start"}]);var e=(new u).getRules();for(var t in e)this.$rules[t]?this.$rules[t]=this.$rules[t].concat(e[t]):this.$rules[t]=e[t]};r.inherits(d,i);t.CFMarkdownHighlightRules=d});