<?php if (count($data) !== 0): ?>
    <h3>
        Список проектов
    </h3>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Проект</th>
                <th scope="col">Описание</th>
                <th scope="col">Статус</th>
                <th scope="col">Дата создания</th>
                <th scope="col">Дата завершения</th>
                <th scope="col">Удаление</th>
                <th scope="col">Изменение</th>
            </tr>
        </thead>
        <?php foreach ($data as $row):
            $created_timestamp = date('d.m.Y', strtotime($row['created_at']));
            $ended_timestamp = !is_null($row['ended_at']) ? date('d.m.Y', strtotime($row['ended_at'])) : "Не завершено";
        ?>
            <tr>
                <td><a href=<?= "$address/pages/projects/card?id=" . $row["id"] ?>><?= htmlspecialchars($row["name"]) ?></td>
                <td><?= getExcerpt(htmlspecialchars($row["description"]), 50) ?></td>
                <td><?= ($row["is_ended"] ? "Завершено" : "В процессе") ?></td>

                <td><?= $created_timestamp ?></td>
                <td><?= $ended_timestamp ?></td>
                <td>
                    <a href="http://localhost/pages/projects/update?id=" <?= $row["id"] ?> class="btn btn-success">
                        Изменить проект
                    </a>
                </td>
                <td>
                    <form method="post" action="delete.php" onkeydown="return event.key !== 'Enter';">
                        <input type="hidden" name="id" value='<?= $row["id"] ?>'>
                        <button type="submit" class="btn btn-danger">
                            Удалить проект
                        </button>
                    </form>
                </td>

            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <h3 class="text-center">Проектов не существует</h1>
    <?php endif; ?>