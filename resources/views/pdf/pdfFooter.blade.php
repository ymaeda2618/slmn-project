<head>
    <meta charset="UTF-8">
</head>

<body class="bg-white">
    <div class="text-center">
        <span id="pdfkit_page_current"></span>
    </div>
    <script type="text/javascript">
        (function() {
            var pdfInfo = {};
            var x = document.location.search.substring(1).split('&');
            for (var i in x) {
                var z = x[i].split('=', 2);
                pdfInfo[z[0]] = unescape(z[1]);
            }
            var page = pdfInfo.page || 1; // ページ番号
            var pageCount = pdfInfo.topage || 1; // 総ページ数
            // 改ページがある場合のみページ番号を表示します
            if (pageCount > 1) {
                document.getElementById('pdfkit_page_current').textContent = page;
            }
        })();
    </script>
</body>

</html>
