<tr>
    <td><?php echo Template::Q($data->id); ?></td>
    <td><?php echo Template::Q($data->subject); ?></td>
    <td><?php echo Template::Q($data->message); ?></td>
    <td><?php echo Utilities::formatDate($data->created); ?></td>
    <td><?php echo Template::Q($data->status); ?></td>
</tr>
