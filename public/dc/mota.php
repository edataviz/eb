<html xmlns:v="urn:schemas-microsoft-com:vml"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns:st1="urn:schemas-microsoft-com:office:smarttags"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1252">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
<link rel=File-List href="mota_files/filelist.xml">
<title>EnergyBuilder Mobile Data Capture API</title>
<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
 name="State"/>
<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
 name="City"/>
<o:SmartTagType namespaceuri="urn:schemas-microsoft-com:office:smarttags"
 name="place"/>
<!--[if gte mso 9]><xml>
 <o:DocumentProperties>
  <o:Author>User</o:Author>
  <o:LastAuthor>User</o:LastAuthor>
  <o:Revision>2</o:Revision>
  <o:TotalTime>11093</o:TotalTime>
  <o:Created>2018-06-06T09:49:00Z</o:Created>
  <o:LastSaved>2018-06-06T09:49:00Z</o:LastSaved>
  <o:Pages>1</o:Pages>
  <o:Words>1129</o:Words>
  <o:Characters>6436</o:Characters>
  <o:Company>HOME</o:Company>
  <o:Lines>53</o:Lines>
  <o:Paragraphs>15</o:Paragraphs>
  <o:CharactersWithSpaces>7550</o:CharactersWithSpaces>
  <o:Version>11.9999</o:Version>
 </o:DocumentProperties>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:WordDocument>
  <w:GrammarState>Clean</w:GrammarState>
  <w:PunctuationKerning/>
  <w:ValidateAgainstSchemas/>
  <w:SaveIfXMLInvalid>false</w:SaveIfXMLInvalid>
  <w:IgnoreMixedContent>false</w:IgnoreMixedContent>
  <w:AlwaysShowPlaceholderText>false</w:AlwaysShowPlaceholderText>
  <w:Compatibility>
   <w:BreakWrappedTables/>
   <w:SnapToGridInCell/>
   <w:WrapTextWithPunct/>
   <w:UseAsianBreakRules/>
   <w:DontGrowAutofit/>
  </w:Compatibility>
  <w:BrowserLevel>MicrosoftInternetExplorer4</w:BrowserLevel>
 </w:WordDocument>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <w:LatentStyles DefLockedState="false" LatentStyleCount="156">
 </w:LatentStyles>
</xml><![endif]--><!--[if !mso]><object
 classid="clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D" id=ieooui></object>
<style>
st1\:*{behavior:url(#ieooui) }
</style>
<![endif]-->
<style>
<!--
 /* Style Definitions */
 p.MsoNormal, li.MsoNormal, div.MsoNormal
	{mso-style-parent:"";
	margin:0in;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:12.0pt;
	font-family:"Times New Roman";
	mso-fareast-font-family:"Times New Roman";}
pre
	{margin:0in;
	margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:"Courier New";
	mso-fareast-font-family:"Times New Roman";}
span.GramE
	{mso-style-name:"";
	mso-gram-e:yes;}
@page Section1
	{size:8.5in 11.0in;
	margin:.75in .5in 63.0pt 1.25in;
	mso-header-margin:.5in;
	mso-footer-margin:.5in;
	mso-paper-source:0;}
div.Section1
	{page:Section1;}
-->
</style>
<!--[if gte mso 10]>
<style>
 /* Style Definitions */
 table.MsoNormalTable
	{mso-style-name:"Table Normal";
	mso-tstyle-rowband-size:0;
	mso-tstyle-colband-size:0;
	mso-style-noshow:yes;
	mso-style-parent:"";
	mso-padding-alt:0in 5.4pt 0in 5.4pt;
	mso-para-margin:0in;
	mso-para-margin-bottom:.0001pt;
	mso-pagination:widow-orphan;
	font-size:10.0pt;
	font-family:"Times New Roman";
	mso-ansi-language:#0400;
	mso-fareast-language:#0400;
	mso-bidi-language:#0400;}
</style>
<![endif]--><!--[if gte mso 9]><xml>
 <o:shapedefaults v:ext="edit" spidmax="2050"/>
</xml><![endif]--><!--[if gte mso 9]><xml>
 <o:shapelayout v:ext="edit">
  <o:idmap v:ext="edit" data="1"/>
 </o:shapelayout></xml><![endif]-->
</head>

<body lang=EN-US style='tab-interval:.5in'>

<div class=Section1>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><span
style='font-size:16.0pt;mso-bidi-font-size:12.0pt'>EnergyBuilder Mobile Data
Capture<o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-size:14.0pt;mso-bidi-font-size:12.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><span
style='font-size:15.0pt;mso-bidi-font-size:12.0pt'>A. Mô t&#7843; l&#432;u
thông tin<o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-size:14.0pt;mso-bidi-font-size:12.0pt'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal>Nh&#7919;ng giá tr&#7883; c&#7847;n l&#432;u trong h&#7879;
th&#7889;ng (s&#7917; d&#7909;ng c&#417; ch&#7871; l&#432;u ki&#7875;u key -
value, trong &#273;ó value là string ho&#7863;c json d&#432;&#7899;i d&#7841;ng
string)</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'>server_address</b>:
string, ví d&#7909;: https://energybuilder.co, là &#273;&#7883;a ch&#7881;
server &#273;&#7875; g&#7885;i các API, giá tr&#7883; m&#7863;c &#273;&#7883;nh
là empty (tr&#7889;ng)</p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'>history_days</b>:
number, s&#7889; ngày l&#7845;y d&#7919; li&#7879;u l&#7883;ch s&#7917;,
&#273;&#432;&#7907;c g&#7917;i kèm <span class=GramE>theo</span> API Download
Config, giá tr&#7883; m&#7863;c &#273;&#7883;nh là 7</p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'>dc_data_type</b>:
string, nh&#7853;n giá tr&#7883; <b style='mso-bidi-font-weight:normal'>fdc</b>
ho&#7863;c <span class=GramE><b style='mso-bidi-font-weight:normal'>std</b></span>,
&#273;&#432;&#7907;c g&#7917;i kèm theo API Download Config, giá tr&#7883;
m&#7863;c &#273;&#7883;nh là fdc</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><u>* Sau khi Login
thành công thì 2 giá tr&#7883; sau c&#7847;n &#273;&#432;&#7907;c l&#432;u
l&#7841;i<o:p></o:p></u></b></p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'>username</b>:
string, tên &#273;&#259;ng nh&#7853;p</p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'>access_token</b>:
string, token tr&#7843; v&#7873; t&#7915; API Login, dùng &#273;&#7875;
g&#7917;i kèm <span class=GramE>theo</span> API Upload Data</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><u>* H&#7879;
th&#7889;ng c&#7847;n &#273;&#7883;nh ngh&#297;a s&#7861;n các json h&#7857;ng
s&#7889; sau <o:p></o:p></u></b></p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'><span
style='color:#222222;background:white'>object_types</span></b><span
style='color:#222222;background:white'>: json, 4 lo&#7841;i object<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'>&quot;<b style='mso-bidi-font-weight:normal'>object_types</b>&quot;:
{<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;FL&quot;:&quot;Flow&quot;,<span
style='mso-tab-count:2'>       </span>lo&#7841;i object Flow<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;EU&quot;:&quot;Energy
Unit&quot;,<span style='mso-tab-count:1'> </span>lo&#7841;i object Energy Unit<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;TA&quot;:&quot;Tank&quot;,<span
style='mso-tab-count:2'>       </span>lo&#7841;i object Tank<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;EQ&quot;:&quot;Equipment&quot;<span
style='mso-tab-count:1'>   </span>lo&#7841;i object Equipment<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'>}<span style='background:white'><o:p></o:p></span></span></p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'>data_types</b>:
json, các ki&#7875;u d&#7919; li&#7879;u</p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'>&quot;data_types&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;<span
class=GramE>n</span>&quot;:&quot;Number&quot;,<span style='mso-tab-count:2'>      </span>ki&#7875;u
s&#7889;<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;<span
class=GramE>t</span>&quot;:&quot;Text&quot;,<span style='mso-tab-count:2'>        </span>ki&#7875;u
text<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;<span
class=GramE>d</span>&quot;:&quot;Date&quot;<span style='mso-tab-count:2'>         </span>ki&#7875;u
ngày<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'>}<o:p></o:p></span></p>

<p class=MsoNormal>+) <b style='mso-bidi-font-weight:normal'>control_types</b>:
json, các ki&#7875;u control nh&#7853;p d&#7919; li&#7879;u</p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'>&quot;control_types&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;<span
class=GramE>n</span>&quot;:&quot;Number input&quot;,<span style='mso-tab-count:
1'> </span>box nh&#7853;p s&#7889;<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;<span
class=GramE>t</span>&quot;:&quot;Text input&quot;,<span style='mso-tab-count:
1'>  </span>box nh&#7853;p text<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;<span
class=GramE>d</span>&quot;:&quot;Date picker&quot;,<span style='mso-tab-count:
1'> </span>ch&#7885;n ngày tháng<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>    </span>&quot;<span
class=GramE>l</span>&quot;:&quot;List&quot;<span style='mso-tab-count:2'>         </span>dropdown
&#273;&#7875; ch&#7885;n t&#7915; danh m&#7909;c<o:p></o:p></span></p>

<p class=MsoNormal style='margin-left:45.0pt'><span style='font-family:"Courier New";
color:#3366FF'>}<o:p></o:p></span></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><u><o:p><span
 style='text-decoration:none'>&nbsp;</span></o:p></u></b></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><u>* Sau khi Download
Config thành công thì các giá tr&#7883; sau c&#7847;n &#273;&#432;&#7907;c
l&#432;u l&#7841;i<o:p></o:p></u></b></p>

<p class=MsoNormal><span style='color:#222222;background:white'>+) routes:
json, thông tin các route<o:p></o:p></span></p>

<p class=MsoNormal><span style='color:#222222;background:white'>+) points:
json, thông tin các point<o:p></o:p></span></p>

<p class=MsoNormal><span style='color:#222222;background:white'>+) objects:
json, thông tin các object<o:p></o:p></span></p>

<p class=MsoNormal>+) object_attrs: json, các thu&#7897;c tính c&#7911;a các
lo&#7841;i object</p>

<p class=MsoNormal><span style='color:#222222;background:white'>+)
object_details</span>: json, data c&#7911;a các object <span class=GramE>theo</span>
key</p>

<p class=MsoNormal>+) lists: json, các danh m&#7909;c d&#7919; li&#7879;u
dictionary</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><u>* Quan h&#7879;:<o:p></o:p></u></b></p>

<p class=MsoNormal>- M&#7897;t route g&#7891;m nhi&#7873;u point, 1 point
g&#7891;m nhi&#7873;u object</p>

<p class=MsoNormal>- M&#7897;t object có th&#7875; &#273;&#7891;ng th&#7901;i
t&#7891;n t&#7841;i &#7903; các point khác nhau</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><u>* Gi&#7843;i thích
chi ti&#7871;t các c&#7845;u trúc json<o:p></o:p></u></b></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>1) </b><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;</span></b><span class=GramE><b style='mso-bidi-font-weight:
normal'><span style='font-family:"Courier New"'>lists</span></b></span><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;: {<o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'>&quot;</span><span
style='font-family:"Courier New"'>CODE_FLOW_PHASE<span style='color:#3366FF'>&quot;:{<o:p></o:p></span></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;1&quot;:&quot;Oil&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;2&quot;:&quot;Gas&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;3&quot;:&quot;Water&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;6&quot;:&quot;NGL&quot;<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'>&quot;</span><span
style='font-family:"Courier New"'>CODE_EVENT_TYPE<span style='color:#3366FF'>&quot;:{<o:p></o:p></span></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;1&quot;:&quot;Producing&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;2&quot;:&quot;Injecting&quot;<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'>&quot;</span><span
style='font-family:"Courier New"'>CODE_EQP_OFFLINE_REASON<span
style='color:#3366FF'>&quot;:{<o:p></o:p></span></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;1&quot;:&quot;1 Electrical-Motor&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;2&quot;:&quot;2 <span class=GramE>Electrical-Alternator</span>&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;3&quot;:&quot;3 <span class=GramE>Mechanical-Engine</span>&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;4&quot;:&quot;4 <span class=GramE>Mechanical-Compressor</span>&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;5&quot;:&quot;5 <span class=GramE>Instrument-Engine</span>&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;6&quot;:&quot;6 <span class=GramE>Instrument-Compressor</span>&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;7&quot;:&quot;7 Unknown&quot;<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>}<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'>}<o:p></o:p></span></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>2) </b><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;</span></b><span class=GramE><b style='mso-bidi-font-weight:
normal'><span style='font-family:"Courier New"'>routes</span></b></span><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;:{<o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;R_1&quot;:<span style='mso-tab-count:
4'>                  </span></span>key c&#7911;a route<span style='font-family:
"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>id</span>&quot;:&quot;1&quot;,<span
style='mso-tab-count:2'>     </span><span style='mso-tab-count:1'>     </span></span>id
c&#7911;a route<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>name</span>&quot;:&quot;Route
1&quot;,<span style='mso-tab-count:1'>  </span></span>tên route<span
style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>complete</span>&quot;:0,<span
style='mso-tab-count:2'>      </span></span>s&#7889; l&#432;&#7907;ng point
&#273;ã nh&#7853;p xong data trong route<span style='font-family:"Courier New";
color:#3366FF'> <o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>total</span>&quot;:&quot;2&quot;<span
style='mso-tab-count:2'>        </span></span>t&#7893;ng s&#7889; point trong
route<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;R_2&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>id</span>&quot;:&quot;2&quot;,&quot;name&quot;:&quot;Route
2&quot;,&quot;complete&quot;:0,&quot;total&quot;:&quot;1&quot;<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>}<o:p></o:p></span></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>3) </b><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;</span></b><span class=GramE><b style='mso-bidi-font-weight:
normal'><span style='font-family:"Courier New"'>points</span></b></span><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;:{<o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;P_1&quot;:<span style='mso-tab-count:
4'>                  </span></span>point key<span style='font-family:"Courier New";
color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>id</span>&quot;:&quot;1&quot;,<span
style='mso-tab-count:2'>     </span><span style='mso-tab-count:1'>     </span></span>point
ID<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;route_id&quot;:&quot;1&quot;,<span
style='mso-tab-count:1'>    </span></span>route ID c&#7911;a point<span
style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>name</span>&quot;:&quot;Point
1.1&quot;,<span style='mso-tab-count:1'> </span></span>tên point<span
style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>complete</span>&quot;:false,<span
style='mso-tab-count:1'>  </span></span>tr&#7841;ng thái &#273;ã nh&#7853;p
xong data (true) hay ch&#432;a (false)<span style='font-family:"Courier New";
color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;FL&quot;:3,<span
style='mso-tab-count:2'>       </span><span style='mso-tab-count:1'>     </span></span>s&#7889;
l&#432;&#7907;ng Flow c&#7911;a point<span style='font-family:"Courier New";
color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;EU&quot;:4,<span
style='mso-tab-count:2'>       </span><span style='mso-tab-count:1'>     </span></span>s&#7889;
l&#432;&#7907;ng Energy Unit<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;TA&quot;:2,<span
style='mso-tab-count:2'>       </span><span style='mso-tab-count:1'>     </span></span>s&#7889;
l&#432;&#7907;ng Tank<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;EQ&quot;:2,<span
style='mso-tab-count:2'>       </span><span style='mso-tab-count:1'>     </span></span>s&#7889;
l&#432;&#7907;ng Equipment<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;objects&quot;:[&quot;FL_373&quot;,
&quot;FL_363&quot;, &quot;FL_374&quot;, &quot;EU_204&quot;, &quot;EU_205&quot;,
&quot;EU_206&quot;, &quot;EU_207&quot;, &quot;TA_25&quot;, &quot;TA_26&quot;, &quot;EQ_29&quot;,
&quot;EQ_30&quot;],<span style='mso-tab-count:1'>  </span></span>array string
l&#432;u các object key c&#7911;a t&#7845;t c&#7843; các lo&#7841;i object
c&#7911;a point<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;P_2&quot;:{&quot;id&quot;:&quot;2&quot;,&quot;route_id&quot;:&quot;1&quot;,&quot;name&quot;:&quot;Point
1.2<span class=GramE>&quot; ...</span><o:p></o:p></span></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>4) </b><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;</span></b><span class=GramE><b style='mso-bidi-font-weight:
normal'><span style='font-family:"Courier New"'>objects</span></b></span><b
style='mso-bidi-font-weight:normal'><span style='font-family:"Courier New";
color:#3366FF'>&quot;:{<o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;FL_373&quot;:<span style='mso-tab-count:
3'>          </span><span style='mso-tab-count:1'>     </span></span>object key
(= object_type + '_' + object_id)<span style='font-family:"Courier New";
color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>id</span>&quot;:&quot;373&quot;,<span
style='mso-tab-count:2'>        </span></span>object ID<span style='font-family:
"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>name</span>&quot;:&quot;CTP
Total Processed Gas&quot;,<span style='mso-tab-count:2'>     </span></span>tên
object<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>type</span>&quot;:&quot;FL&quot;<span
style='mso-tab-count:2'>        </span></span>lo&#7841;i object (object_type)<span
style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;EU_204&quot;:<span style='mso-tab-count:
4'>               </span></span>object key<span style='font-family:"Courier New";
color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>id</span>&quot;:&quot;204&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>name</span>&quot;:&quot;East
Marine 02&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>&quot;<span class=GramE>type</span>&quot;:&quot;EU&quot;,<o:p></o:p></span></p>

<p class=MsoNormal style='margin-right:-.5in'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>          </span>&quot;event_phases&quot;:{
</span>riêng &#273;&#7889;i v&#7899;i object type là EU thì có thêm thu&#7897;c
tính event_phases</p>

<p class=MsoNormal style='margin-right:-27.0pt'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:3'>              </span>&quot;1&quot;:</span><span
style='font-size:10.0pt;font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'> </span></span><span style='font-size:10.0pt'>event = 1
(tra <span class=GramE>theo</span> list CODE_EVENT_TYPE v&#7899;i key=1 thì
s&#7869; có NAME = Producing)<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:3'>              </span>[<o:p></o:p></span></p>

<p class=MsoNormal style='margin-right:-.5in'><span style='font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:4'>                   </span>&quot;1&quot;,<span
style='mso-tab-count:1'> </span></span><span style='font-size:10.0pt'>phase = 1
(tra <span class=GramE>theo</span> list CODE_FLOW_PHASE v&#7899;i key=1 thì
s&#7869; có NAME = Oil)<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:4'>                   </span>&quot;2&quot;, </span>phase
Gas<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:4'>                   </span>&quot;3&quot;<span
style='mso-tab-count:1'>  </span></span>phase Water<span style='font-family:
"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:3'>              </span>],<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:3'>              </span>&quot;2&quot;<span class=GramE>:[</span>&quot;21&quot;]<span
style='mso-tab-count:1'>    </span></span>event Injecting (2), phase Gas Lift
(21)<span style='font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:3'>              </span>}<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:2'>          </span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-family:"Courier New";color:#3366FF'><span
style='mso-tab-count:1'>     </span>&quot;EU_205&quot;: ...<o:p></o:p></span></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><i style='mso-bidi-font-style:
normal'><o:p>&nbsp;</o:p></i></b></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><i style='mso-bidi-font-style:
normal'>Gi&#7843;i thích:</i></b> v&#7899;i giá tr&#7883; c&#7911;a
event_phases nh&#432; trên thì s&#7869; &#273;&#432;&#7907;c hi&#7875;u r&#7857;ng:
Object 'East Marine 02' tham gia vào 2 lo&#7841;i ho&#7841;t &#273;&#7897;ng
(event) là <span class=GramE>Producing</span> và Injecting. V&#7899;i
producing, nó s&#7869; produce 3 thành ph&#7847;n (phase) là Oil, Gas, Water.
V&#7899;i Injecting, nó s&#7869; inject 1 thành ph&#7847;n là Gas Lift.</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>5) </b><b
style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt;font-family:
"Courier New";color:#3366FF'>&quot;</span></b><b style='mso-bidi-font-weight:
normal'><span style='font-family:"Courier New"'>object_attrs</span></b><b
style='mso-bidi-font-weight:normal'><span style='font-size:10.0pt;font-family:
"Courier New";color:#3366FF'>&quot;:{<o:p></o:p></span></b></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'>&quot;EQ&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>      </span>&quot;BEGIN_READING_VALUE&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>name</span>&quot;:&quot;Yesterday Hours Read&quot;,<span
style='mso-tab-count:1'>      </span></span><span style='mso-bidi-font-size:
10.0pt'>Tên hi&#7875;n th&#7883; trên giao di&#7879;n<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;n&quot;,<span
style='mso-tab-count:4'>                    </span></span><span
style='mso-bidi-font-size:10.0pt'>Ki&#7875;u d&#7919; li&#7879;u là Number</span><span
style='font-size:10.0pt;font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;n&quot;,<span
style='mso-tab-count:3'>                 </span></span><span style='mso-bidi-font-size:
10.0pt'>Ki&#7875;u control là Input Number</span><span style='font-size:10.0pt;
font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>enable</span>&quot;:true,<span style='mso-tab-count:4'>                      </span></span><span
style='mso-bidi-font-size:10.0pt'>Có hi&#7879;u l&#7921;c, cho phép nh&#7853;p
data</span><span style='font-size:10.0pt;font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>format</span>&quot;:&quot;000000.##&quot;,<span style='mso-tab-count:4'>                      </span></span><span
style='mso-bidi-font-size:10.0pt'>format string (not used)</span><span style='font-size:10.0pt;font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>decimals</span>&quot;:2,<span style='mso-tab-count:4'>                      </span></span><span
style='mso-bidi-font-size:10.0pt'>So chu so o phan thap phan</span><span style='font-size:10.0pt;font-family:"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;mandatory&quot;<span
class=GramE>:false</span>},<span style='mso-tab-count:3'>                 </span></span><span
style='mso-bidi-font-size:10.0pt'>true/false: B&#7855;t bu&#7897;c ph&#7843;i
nh&#7853;p s&#7889; li&#7879;u ho&#7863;c không, dùng &#273;&#7875; validating
tr&#432;&#7899;c khi Save data</span><span style='font-size:10.0pt;font-family:
"Courier New";color:#3366FF'><o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>      </span>&quot;END_READING_VALUE&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>name</span>&quot;:&quot;Today Hours Read&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>enable</span>&quot;:true,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;mandatory&quot;<span
class=GramE>:false</span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:1'>      </span>&quot;OFFLINE_REASON_CODE&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>name</span>&quot;:&quot;Reason Code&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;t&quot;,<o:p></o:p></span></p>

<p class=MsoNormal style='margin-right:-27.0pt'><span style='font-size:10.0pt;
font-family:"Courier New";color:#FF6600'><span style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;l&quot;,<span
style='mso-tab-count:2'>           </span></span><span style='mso-bidi-font-size:
10.0pt'>Ki&#7875;u control là list: hi&#7875;n th&#7883; dropdown &#273;&#7875;
ch&#7885;n<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>enable</span>&quot;:true,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New";
color:#3366FF'><span style='mso-tab-count:2'>            </span>&quot;<span
class=GramE>mandatory</span>&quot;:false,<o:p></o:p></span></p>

<p class=MsoNormal style='margin-right:-.5in'><span style='font-size:10.0pt;
font-family:"Courier New";color:#FF6600'><span style='mso-tab-count:2'>            </span>&quot;list&quot;:&quot;CODE_EQP_OFFLINE_REASON&quot;},<span
style='mso-tab-count:1'>  </span></span><span style='mso-bidi-font-size:10.0pt'>Tên
list s&#7917; d&#7909;ng &#273;&#7875; build dropdown<span style='color:#FF6600'><o:p></o:p></span></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:1'>      </span>&quot;TEMP&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>name</span>&quot;:&quot;Avg
Temp (Celsius)&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;t&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;t&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>enable</span>&quot;:true,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;mandatory&quot;<span
class=GramE>:false</span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:1'>      </span>&quot;PRESS&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>name</span>&quot;:&quot;Avg
Press (barg)&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>enable</span>&quot;:true,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;mandatory&quot;<span
class=GramE>:false</span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:1'>      </span>&quot;EQP_NOTE&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>name</span>&quot;:&quot;Comment&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;t&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;t&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>enable</span>&quot;:true,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;mandatory&quot;<span
class=GramE>:false</span>}<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:1'>      </span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'>&quot;EU&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:1'>      </span>&quot;ACTIVE_HRS&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>name</span>&quot;:&quot;On
Strm Hrs&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>enable</span>&quot;:true,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;mandatory&quot;<span
class=GramE>:false</span>},<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:1'>      </span>&quot;EU_DATA_GRS_VOL&quot;:{<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>name</span>&quot;:&quot;Grs
Vol. (scm)&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;data_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;control_type&quot;:&quot;n&quot;,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;<span class=GramE>enable</span>&quot;:true,<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;font-family:"Courier New"'><span
style='mso-tab-count:2'>            </span>&quot;mandatory&quot;<span
class=GramE>:false</span>},<o:p></o:p></span></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal>Gi&#7843;i thích: object_attrs mô t&#7843; chi ti&#7871;t
các thu&#7897;c tính c&#7911;a t&#7915;ng lo&#7841;i object. Ví d&#7909;
v&#7899;i lo&#7841;i object EQ (Quipment) là bao g&#7891;m các thu&#7897;c tính
<span style='font-size:10.0pt;font-family:"Courier New"'>BEGIN_READING_VALUE, END_READING_VALUE,
OFFLINE_REASON_CODE, TEMP, PRESS, <span class=GramE>EQP</span>_NOTE.</span><span
style='font-size:10.0pt'><o:p></o:p></span></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<pre><b style='mso-bidi-font-weight:normal'><span style='font-size:12.0pt;
mso-bidi-font-size:10.0pt'>6) <span style='color:black'>&quot;object_details&quot;:{<o:p></o:p></span></span></b></pre><pre><span
style='color:#3366FF'>&quot;EQ_29_2018-06-01&quot;:{<o:p></o:p></span></pre><pre><span
style='color:#3366FF'><span style='mso-tab-count:1'>        </span>&quot;BEGIN_READING_VALUE&quot;:&quot;74.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:#3366FF'><span style='mso-tab-count:1'>        </span>&quot;END_READING_VALUE&quot;:&quot;72.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:#3366FF'><span style='mso-tab-count:1'>        </span>&quot;OFFLINE_REASON_CODE&quot;:&quot;6&quot;,<o:p></o:p></span></pre><pre><span
style='color:#3366FF'><span style='mso-tab-count:1'>        </span>&quot;TEMP&quot;:&quot;85.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:#3366FF'><span style='mso-tab-count:1'>        </span>&quot;PRESS&quot;:&quot;84.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:#3366FF'><span style='mso-tab-count:1'>        </span>&quot;EQP_NOTE&quot;:&quot;&quot;},<o:p></o:p></span></pre><pre><span
style='color:black'>&quot;EQ_29_2018-06-02&quot;:{<o:p></o:p></span></pre><pre><span
style='color:black'><span style='mso-tab-count:1'>        </span>&quot;BEGIN_READING_VALUE&quot;:&quot;74.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:black'><span style='mso-tab-count:1'>        </span>&quot;END_READING_VALUE&quot;:&quot;74.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:black'><span style='mso-tab-count:1'>        </span>&quot;OFFLINE_REASON_CODE&quot;:&quot;2&quot;,<o:p></o:p></span></pre><pre><span
style='color:black'><span style='mso-tab-count:1'>        </span>&quot;TEMP&quot;:&quot;84.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:black'><span style='mso-tab-count:1'>        </span>&quot;PRESS&quot;:&quot;85.00&quot;,<o:p></o:p></span></pre><pre><span
style='color:black'><span style='mso-tab-count:1'>        </span>&quot;EQP_NOTE&quot;:&quot;Hdjfnf&quot;},<o:p></o:p></span></pre><pre><span
class=GramE><span style='color:black'>&quot;EQ_29_2018-06-03&quot;:{...}</span></span><span
style='color:black'><o:p></o:p></span></pre>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal style='margin-right:-27.0pt'>Gi&#7843;i thích:
object_details l&#432;u tr&#7919; data c&#7911;a các object <span class=GramE>theo</span>
key. M&#7895;i key &#273;&#432;&#7907;c k&#7871;t h&#7907;p <span class=GramE>theo</span>
nguyên t&#7855;c sau:</p>

<p class=MsoNormal>&#272;&#7889;i v&#7899;i <st1:place w:st="on"><st1:City
 w:st="on">EQ</st1:City>, <st1:State w:st="on">FL</st1:State></st1:place>, TA
thì <span style='mso-tab-count:1'>         </span>key = [object_type] _
[object_id] _ [date]</p>

<p class=MsoNormal>&#272;&#7889;i v&#7899;i EU thì <span style='mso-tab-count:
2'>                       </span>key = [object_type] _ [object_id] _ [date] _
[phase] _ [event]</p>

<p class=MsoNormal>([<span class=GramE>date</span>] có d&#7841;ng YYYY-MM-DD)</p>

<p class=MsoNormal>Ví d&#7909;:<span style='font-family:"Courier New"'> </span><span
style='font-size:10.0pt;mso-bidi-font-size:12.0pt;font-family:"Courier New";
color:#3366FF'>&quot;EQ_29_2018-06-01&quot;, &quot;EU_105_2018-06-01_2_1&quot;<o:p></o:p></span></p>

<p class=MsoNormal><span style='font-size:10.0pt;mso-bidi-font-size:12.0pt;
font-family:"Courier New";color:#3366FF'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='color:#3366FF'>L&#432;u ý: Trong giá tr&#7883;
c&#7911;a object detail, ngoài các giá tr&#7883; c&#7911;a các field, m&#7895;i
khi data &#273;&#432;&#7907;c thay &#273;&#7893;i thì c&#7847;n b&#7893; sung
thêm thu&#7897;c tính <b style='mso-bidi-font-weight:normal'>editted</b>
&#273;&#7875; phân bi&#7879;t. M&#7909;c &#273;ích là khi Upload data lên
server thì ch&#7881; post lên nh&#7919;ng data nào &#273;ã b&#7883; thay
&#273;&#7893;i. <span class=GramE>Nh&#7919;ng data không thay &#273;&#7893;i
thì không c&#7847;n post.</span><o:p></o:p></span></p>

<p class=MsoNormal><span style='color:#3366FF'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><span style='color:#3366FF'>Ví d&#7909;:<o:p></o:p></span></p>

<pre>&quot;EQ_29_2018-06-01&quot;:{</pre><pre><span style='mso-tab-count:1'>        </span>&quot;BEGIN_READING_VALUE&quot;:&quot;<span
style='color:#FF6600'>75.00</span>&quot;,</pre><pre><span style='mso-tab-count:
1'>        </span>&quot;END_READING_VALUE&quot;:&quot;72.00&quot;,</pre><pre><span
style='mso-tab-count:1'>        </span>&quot;OFFLINE_REASON_CODE&quot;:&quot;6&quot;,</pre><pre><span
style='mso-tab-count:1'>        </span>&quot;TEMP&quot;:&quot;85.00&quot;,</pre><pre><span
style='mso-tab-count:1'>        </span>&quot;PRESS&quot;:&quot;84.00&quot;,</pre><pre><span
style='mso-tab-count:1'>        </span>&quot;EQP_NOTE&quot;:&quot;<span
style='color:#FF6600'>changed</span>&quot;,</pre><pre><span style='mso-tab-count:
1'>        </span><span style='color:#FF6600'>&quot;<span class=GramE>editted</span>&quot;:&quot;1&quot;<o:p></o:p></span></pre><pre>},</pre>

<p class=MsoNormal><span style='color:#3366FF'><o:p>&nbsp;</o:p></span></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><span
style='font-size:15.0pt;mso-bidi-font-size:12.0pt'>B. Mô t&#7843; API<o:p></o:p></span></b></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><o:p>&nbsp;</o:p></b></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>1) Login<o:p></o:p></b></p>

<p class=MsoNormal><span class=GramE>address</span>: <b style='mso-bidi-font-weight:
normal'>server_address</b> + <b style='mso-bidi-font-weight:normal'>/dclogin</b></p>

<p class=MsoNormal><span class=GramE>request</span> type: post</p>

<p class=MsoNormal><span class=GramE>param</span>: {username='string',
password='string'}</p>

<p class=MsoNormal><span class=GramE>return</span>: {message='string', access_token
='string'}</p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span><span
class=GramE>login</span> successfully: message='ok'</p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span><span
class=GramE>login</span> fail: message=error message</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>2) Download Config<o:p></o:p></b></p>

<p class=MsoNormal><span class=GramE>address</span>: <b style='mso-bidi-font-weight:
normal'>server_address</b> + /<b style='mso-bidi-font-weight:normal'>dcloadconfig</b></p>

<p class=MsoNormal><span class=GramE>request</span> type: post</p>

<p class=MsoNormal><span class=GramE>param</span>: {</p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span>token
=<b style='mso-bidi-font-weight:normal'>'access_token'</b>, </p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span>data_type =<b
style='mso-bidi-font-weight:normal'> 'dc_data_type'</b>,</p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span><span
class=GramE>days</span> =<b style='mso-bidi-font-weight:normal'> history_days<o:p></o:p></b></p>

<p class=MsoNormal>}</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><span class=GramE>return</span>: json (tham kh&#7843;o https://energybuilder.co/dc/response.php)</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'>3) Upload Data<o:p></o:p></b></p>

<p class=MsoNormal><span class=GramE>address</span>: <b style='mso-bidi-font-weight:
normal'>server_address</b> + <b style='mso-bidi-font-weight:normal'>/dcsavedata<o:p></o:p></b></p>

<p class=MsoNormal><span class=GramE>param</span>: {</p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span>token
=<b style='mso-bidi-font-weight:normal'>'access_token'</b>, </p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span>data_type =<b
style='mso-bidi-font-weight:normal'> 'dc_data_type'</b>, </p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span>object_details<span
class=GramE>=<b style='mso-bidi-font-weight:normal'>{</b></span><b
style='mso-bidi-font-weight:normal'>object_details}<o:p></o:p></b></p>

<p class=MsoNormal>}</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><span class=GramE>return</span>: {message='string'}</p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span><span
class=GramE>successfully</span>: message='ok'</p>

<p class=MsoNormal><span style='mso-tab-count:1'>            </span><span
class=GramE>fail</span>: message=error message</p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

<p class=MsoNormal><b style='mso-bidi-font-weight:normal'><span
style='font-size:15.0pt;mso-bidi-font-size:12.0pt'>C. Mô t&#7843; giao
di&#7879;n (&#273;ang c&#7853;p nh&#7853;t)<o:p></o:p></span></b></p>

<p class=MsoNormal><o:p>&nbsp;</o:p></p>

</div>

</body>

</html>
