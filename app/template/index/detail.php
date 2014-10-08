<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../../stylesheet/css/bootstrap/bootstrap.min.css">
        <link rel="stylesheet" href="../../stylesheet/css/detail.css" type="text/css">

        <title>Product Detail</title>

    </head>
    <body>

        <div id="detail-summary" >
            <img src="<?=$product->thumbImages?>"/>
            <strong>
                <?=$product->name?>
                <br>
            </strong>
            <strong>品牌：</strong>
            <?=$product->brand?>
            <br>
            <strong>类别：</strong>
            <?=$product->category?>
            <br>
            <strong>价格：￥</strong>
            <?=$product->price?>
            <br>
            <strong>风格：</strong>
            <?=$product->style?>
            <br>
            <strong>颜色：</strong>
            <?=$product->color?>
            <br>

        </div>

        <hr>

        <div class="container">

            <div id="detail-title-row" class="row">
                <div class="col-lg-2">
                    <strong> YOU MAY ALSO LIKE </strong>

                </div>

                <div class="col-lg-10">
                    <strong> DETAILS </strong>
                </div>

            </div>

            <div id="detail-image-row" class="row">

                <div id="recommendation-container" class="col-lg-2">
                    <?foreach($recommendation as $r):?>
                        <a href="detail?id=<?=$r->id?>" class="thumbnail">
                            <img src="<?=$r->thumbImages?>">
                        </a>
                    <?endforeach?>
                </div>


                <div id="images-container" class="col-lg-10">
                    <?foreach($product->detailImages as $url):?>
                        <img src="<?=$url?>" /> <br />
                    <?endforeach?>
                </div>

            </div>
        </div>

        <script src="../../script/js/jquery/jquery.min.jsj"></script>
        <script src="../../script/js/bootstrap/bootstrap.min.js"></script>

    </body>

</html>