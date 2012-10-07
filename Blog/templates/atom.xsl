<?xml version='1.0'?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:atom="http://purl.org/atom/ns#"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  xmlns="http://www.w3.org/1999/xhtml">

<xsl:output indent="no" method="html"/>
<xsl:output doctype-public="-//W3C//DTD HTML 4.01//EN"/>
<xsl:output doctype-system="http://www.w3.org/TR/html4/strict.dtd"/>


<xsl:template match="atom:feed">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head profile="http://gmpg.org/xfn/1">
  <title><xsl:call-template name="pagetitle"/></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style>
	body {
		font-family: sans-serif;
		font-size: 12px;
		margin:0;
		padding:0;
	}

	div.atomtitle {
		background: #37c;
		color: #fff;
		margin:0;
		padding: 10px;	
		font-size: 30px;
		font-weight: bold;
	}
	
	div.atomfeedtitle {
		padding: 5px;
		color: #f90;
		font-size: 14px;
	}
	
	a {
		color: #666;
		text-decoration: none;
	}

	a:hover {
		color: #37c;
		text-decoration: underline;
	}
  </style>
</head>
<body>
<div class="atomtitle"><xsl:call-template name="pagetitle"/></div>
<xsl:apply-templates/>
</body>
</html>
</xsl:template>

<xsl:template match="atom:entry">
  <xsl:variable name="permalink"><xsl:value-of select="atom:link[@rel='alternate' and @type='text/html']/@href" /></xsl:variable>
  <div class="atomfeedtitle"><xsl:apply-templates select="atom:author/atom:name"/>: <a href="{$permalink}"><xsl:apply-templates select="atom:title"/></a></div>
</xsl:template>

<xsl:template match="atom:entry/atom:title | atom:entry/atom:author/atom:name ">
  <xsl:apply-templates/>
</xsl:template>

<xsl:template match="*"/>
        
<xsl:template name="pagetitle">
  <xsl:value-of select="/atom:feed/atom:title"/>
</xsl:template>

</xsl:stylesheet>
