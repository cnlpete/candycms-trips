<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title>{$_WEBSITE.title} - {$WEBSITE_NAME}</title>
    <description>{$lang.website.description}</description>
    <language>{$WEBSITE_LANGUAGE}</language>
    <link>{$WEBSITE_URL}/trips/{$_WEBSITE.id}</link>
    <copyright>{$WEBSITE_NAME}</copyright>
    <pubDate>{$_WEBSITE.date}</pubDate>
    <atom:link href="{$CURRENT_URL}" rel="self" type="application/rss+xml" />
    {foreach $data as $d}
      <item>
        <title>{$d.title}</title>
        <pubDate>{$d.date.rss}</pubDate>
        <description>
          <![CDATA[
            {$d.content}
          ]]>
        </description>
        <dc:creator>{$d.author.full_name}</dc:creator>
        <guid isPermaLink="true">{$d.url_clean}/{$d.id}</guid>
        <link>{$d.url}</link>
      </item>
    {/foreach}
  </channel>
</rss>
