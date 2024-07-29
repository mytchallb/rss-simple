<?php
// URLs of RSS feeds
$feeds = [
    // "Stuff NZ" => "https://www.stuff.co.nz/rss/rss.xml",
    "Google" => "https://news.google.com/rss?hl=en-NZ&gl=NZ&ceid=NZ:en",
    "Al Jazeera" => "https://www.aljazeera.com/xml/rss/all.xml",
    // "NZ Herald" => "https://www.nzherald.co.nz/arc/outboundfeeds/rss/curated/78/?outputType=xml&_website=nzh",
    // "Radio NZ World" => "https://www.rnz.co.nz/rss/world.xml",
    // "Radio NZ Country" => "https://www.rnz.co.nz/rss/country.xml",
    // "NZ Police" => "https://www.police.govt.nz/rss/news",
];

// Function to fetch and parse RSS feed
function fetch_feed($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    curl_setopt($curl, CURLOPT_USERAGENT, 'FeedReader');


    $data = curl_exec($curl);
    curl_close($curl);

    if ($data) {
        return simplexml_load_string($data);
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSS Feeds</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            height: 100vh;
            margin: 0;
            padding: 0;
        }
        .feed-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
        }
        a {
            color: #1e90ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .feed {
            width: 100%;
            padding: 20px;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #1a1a1a;
        }
        .feed h2 {
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .article {
            padding-bottom: 40px;
            cursor: alias;
        }
        .articles {
            height: calc(100% - 50px);
            overflow-y: auto;
        }
        .article h3 {
            margin: 0;
            font-size: 1.1em;
        }
        .article p {
            margin: 5px 0;
        }
        .article.read {
            color: #888;
            opacity: .35;
        }
    </style>
</head>
<body>
    <div class="feed-container">
        <?php foreach ($feeds as $feedTitle => $url): ?>
        <div class="feed">
            <h2><?php echo htmlspecialchars($feedTitle); ?></h2>
            <div class="articles">
                <?php
                $feed = fetch_feed($url);
                $articles = [];
                if ($feed && isset($feed->channel->item)) {
                    foreach ($feed->channel->item as $item) {
                        $link = (string)$item->link;
                        $title = (string)$item->title;
                        $description = (string)$item->description;
                        $guid = (string)$item->guid;
                        $articles[] = compact('link', 'title', 'description', 'guid');
                    }

                    // Output articles without trying to sort them based on read status
                    foreach ($articles as $article) {
                        $link = $article['link'];
                        $title = $article['title'];
                        $description = $article['description'];
                        $guid = $article['guid'];
                        echo "<div class='article' data-guid='$guid'>";
                        echo "<h3><a href='$link' target='_blank'>$title</a></h3>";
                        echo "<p>$description</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Unable to load feed.</p>";
                }
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Check for read status in localStorage and apply styles
            document.querySelectorAll('.article').forEach(article => {
                const guid = article.getAttribute('data-guid');
                if (localStorage.getItem(guid)) {
                    article.classList.add('read');
                }
            });

            // Rearrange articles based on read status
            const rearrangeArticles = () => {
                const feedContainers = document.querySelectorAll('.feed');
                feedContainers.forEach(feedContainer => {
                    const articles = Array.from(feedContainer.querySelectorAll('.article'));
                    articles.sort((a, b) => {
                        const isReadA = localStorage.getItem(a.getAttribute('data-guid')) ? 1 : 0;
                        const isReadB = localStorage.getItem(b.getAttribute('data-guid')) ? 1 : 0;
                        return isReadA - isReadB;
                    });
                    articles.forEach(article => {
                        feedContainer.querySelector('.articles').appendChild(article);
                    });
                });
            };
            rearrangeArticles();

            // Add event listener to mark articles as read
            document.querySelectorAll('.article').forEach(link => {
                link.addEventListener('click', event => {
                    const article = event.target.closest('.article');
                    const guid = article.getAttribute('data-guid');

                    // Check if the article is already marked as read
                    if (article.classList.contains('read')) {
                      // take off read status
                      localStorage.removeItem(guid);
                      article.classList.remove('read');
                    } else {
                      localStorage.setItem(guid, 'true');
                      article.classList.add('read');
                    }
                    rearrangeArticles();
                });
            });
        });
    </script>
</body>
</html>
