<?php
require_once 'App/Infrastructure/sdbh.php';

use sdbh\sdbh;

$dbh = new sdbh();
?>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <div class="row row-header">
            <div class="col-12" id="count">
                <img src="assets/img/logo.png" alt="logo" style="max-height:50px" />
                <h1>Прокат Y</h1>
            </div>
        </div>

        <div class="row row-form">
            <div class="col-12">
                <form action="App/calculate.php" method="POST" id="form">

                    <?php $products = $dbh->make_query('SELECT * FROM a25_products');
                    if (is_array($products)) { ?>
                        <label class="form-label" for="product">Выберите продукт:</label>
                        <select class="form-select" name="product" id="product">
                            <?php foreach ($products as $product) {
                                $name = $product['NAME'];
                                $price = $product['PRICE'];
                                $tarif = $product['TARIFF'];
                            ?>
                                <option value="<?= $product['ID']; ?>"><?= $name; ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?>

                    <label for="customRange1" class="form-label" id="count">Количество дней:</label>
                    <input type="number" name="days" class="form-control" id="customRange1" min="1" max="30">

                    <?php $services = unserialize($dbh->mselect_rows('a25_settings', ['set_key' => 'services'], 0, 1, 'id')[0]['set_value']);
                    if (is_array($services)) {
                    ?>
                        <label for="customRange1" class="form-label">Дополнительно:</label>
                        <?php
                        $index = 0;
                        foreach ($services as $k => $s) {
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="services[]" value="<?= $s; ?>" id="flexCheck<?= $index; ?>">
                                <label class="form-check-label" for="flexCheck<?= $index; ?>">
                                    <?= $k ?>: <?= $s ?>
                                </label>
                            </div>
                        <?php $index++;
                        } ?>
                    <?php } ?>

                    <button type="submit" class="btn btn-primary">Рассчитать</button>
                </form>

                <h5>Итоговая стоимость: <span id="total-price"></span></h5>
                <button id="leaveRequestBtn" class="btn btn-success" style="display:none;" data-bs-toggle="modal" data-bs-target="#requestModal">Оставить заявку</button>

                <!-- Новое окно для заявки -->
                <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="requestModalLabel">Оставить заявку</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="requestForm">
                                    <div class="mb-3">
                                        <label for="phoneNumber" class="form-label">Номер телефона</label>
                                        <input type="text" class="form-control" id="phoneNumber" name="phone" required>
                                    </div>
                                    <input type="hidden" id="orderDetails" name="orderDetails">
                                    <button type="submit" class="btn btn-primary">Оставить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#form").submit(function(event) {
                event.preventDefault();

                $.ajax({
                    url: 'App/calculate.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: (response) => {
                        $("#total-price").text(response);
                        $("#leaveRequestBtn").show();
                        $("#orderDetails").val($(this).serialize());
                    },
                    error: function() {
                        $("#total-price").text('Ошибка при расчете');
                    }
                });
            });

            $("#requestForm").submit(function(event) {
                event.preventDefault();

                const orderDetails = $("#orderDetails").val();

                $.ajax({
                    url: 'App/sendorder.php',
                    type: 'POST',
                    data: {
                        phone: $("input[name='phone']").val(),
                        orderDetails: orderDetails
                    },
                    success: function(response) {
                        alert(response);
                        $('#requestModal').modal('hide');
                    },
                    error: function() {
                        alert('Ошибка при отправке заявки');
                    }
                });
            });
        });
    </script>
</body>

</html>