<main class="dataList" >
    <table>
        <tr>
            <th>Login</th>
            <th>Email</th>
            <th>Pseudo</th>
            <th>Admin</th>
            <th>Modification</th>
            <th>Suppression</th>
        </tr>
        
        <?php foreach ($users as $obj): ?>
            <tr>
                <td><?= htmlspecialchars($obj->getId()) ?></td>
                <td><?= htmlspecialchars($obj->getEmail()) ?></td>
                <td><?= htmlspecialchars($obj->getPseudo()) ?></td>
                <td><?= htmlspecialchars($obj->getIsAdmin()) ?></td>
                <td>
                    <form method="POST" action="/user/edit">
                        <input type="hidden" name="user_id"         value = "<?= htmlspecialchars($obj->getId())            ?>">
                        <input type="hidden" name="user_email"      value = "<?= htmlspecialchars($obj->getEmail())         ?>">
                        <input type="hidden" name="user_pseudo"     value = "<?= htmlspecialchars($obj->getPseudo())        ?>">
                        <input type="hidden" name="user_isAdmin"    value = "<?= htmlspecialchars($obj->getIsAdmin())       ?>">

                        <input type="submit" value="Modifier">
                    </form>
                </td>
                <td>
                    <form method="POST" action="/user/delete">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($obj->getId()) ?>">
                        <input type="submit" value="Supprimer">
                    </form>
                </td>
            </tr>
        <?php endforeach;?>
    </table>
</main>