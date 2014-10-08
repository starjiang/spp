<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../../stylesheet/css/bootstrap/bootstrap.min.css">
        <link rel="stylesheet" href="../../stylesheet/css/list.css">
        <title> Fashion Product List </title>
    </head>

        <body>

            <div style="text-align: center; width: 1100px; margin: auto">
                <nav class="navbar navbar-default" role="navigation">
                    <div class="container-fluid">
                        <!-- Brand and toggle get grouped for better mobile display -->
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a class="navbar-brand" href="#">DEJA fashion</a>
                        </div>

                        <!-- Collect the nav links, forms, and other content for toggling -->
                        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                            <ul class="pager navbar-right">
                                    <?
                                        if ($offset == 0)
                                        {
                                            echo '<li class="previous disabled"><a href="#">Prev</a></li>';
                                        }
                                        else {
                                            echo sprintf('<li class="previous"> <a href="list?offset=%d">Prev</a></li>',
                                                max($offset - $limit, 0));
                                        }

                                        echo sprintf('<li class="next"><a href="list?offset=%d">Next</a></li>', $offset + $limit);
                                    ?>
                            </ul>

                        </div><!-- /.navbar-collapse -->
                    </div><!-- /.container-fluid -->
                </nav>

                <div class="container-fluid">
                    <div class="row">
                        <div id="condition-container" class="col-lg-2">

                            <div class="well well-lg">
                                <ul class="nav ">
                                    <li>
                                        <label class="tree-toggler nav-header">
                                            Category
                                        </label>
                                        <ul class="nav tree active-trial">
                                            <?foreach($category as $c):?>
                                            <li class="radio">
                                                <label> <input type="radio" name="category-radios"><?=$c?></label>
                                            </li>
                                            <?endforeach?>
                                        </ul>
                                    </li>

                                    <li class="nav-divider"></li>

                                    <li>
                                        <label class="tree-toggler nav-header">
                                            Brand
                                        </label>
                                        <ul class="nav tree active-trial">
                                            <?foreach($brand as $b):?>
                                            <li class="radio">
                                                <label> <input type="radio" name="brand-radios"><?=$b?></label>
                                            </li>
                                            <?endforeach?>
                                        </ul>
                                    </li>

                                    <li class="nav-divider"></li>

                                    <li>
                                        <label class="tree-toggler nav-header">
                                            Color
                                        </label>
                                        <ul class="nav tree active-trial">
                                            <?foreach($color as $c):?>
                                            <li class="radio">
                                                <label> <input type="radio" name="color-radios"><?=$c?></label>
                                            </li>
                                            <?endforeach?>
                                        </ul>
                                    </li>

                                    <li class="nav-divider"></li>

                                    <li>
                                        <label class="tree-toggler nav-header">
                                            Size
                                        </label>
                                        <ul class="nav tree active-trial">
                                            <?foreach($size as $s):?>
                                            <li class="radio">
                                                <label> <input type="radio" name="size-radios"><?=$s?></label>
                                            </li>
                                            <?endforeach?>
                                        </ul>
                                    </li>

                                </ul>
                            </div>
                        </div>

                        <div id="item-container" class="col-lg-10">

                            <?foreach($products as $product):?>
                                <div class="col-lg-2" >
                                    <a href="detail?id=<?=$product->id?>"
                                       class="thumbnail"
                                       target="_blank"
                                       data-toggle="tooltip" data-placement="left" title=<?=$product->name?> >

                                        <img src="<?=$product->thumbImages?>"/>

                                    </a>
                                </div>
                            <?endforeach?>

                        </div>

                    </div>
                </div>

            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
            <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

        </body>

</html>