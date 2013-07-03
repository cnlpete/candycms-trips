{strip}
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/create'>
        <i class='icon-plus'
           title='{$lang.global.create.entry}'></i>
        {$lang.trips.title.create}
      </a>
    </p>
  {/if}
  {foreach $trips as $trip}
    <article class='blogs' itemprop='blogPost'>
      <header class='page-header'>
        <h2>
          <a href='{$trip.url}'>{$trip.title}</a>
          {if $_SESSION.user.role >= 3}
            <a href='{$trip.url_update}'>
              <i class='icon-pencil'
                  title='{$lang.global.update.update}'></i>
            </a>
          {/if}
        </h2>
        <p>
          <time datetime='{$trip.start_date.w3c}' class='js-timeago'>
            {$trip.start_date.raw|date_format:$lang.global.time.format.date}
          </time>
          {if isset($trip.end_date)}
            &nbsp;-&nbsp;
            <time datetime='{$trip.end_date.w3c}' class='js-timeago'>
              {$trip.end_date.raw|date_format:$lang.global.time.format.date}
            </time>
          {/if}
          &nbsp;
          {$lang.global.by}
          &nbsp;
          <a href='{$trip.author.url}' rel='author'>{$trip.author.full_name}</a>
        </p>
      </header>
      {if isset($trip.teaser) && $trip.teaser}
        <p class='summary'>
          {$trip.teaser}
        </p>
      {/if}
    </article>
  {/foreach}
{/strip}
