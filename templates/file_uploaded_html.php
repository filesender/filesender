<tr>
    <td><?php echo $data->id; ?></td>
    <td><?php echo $data->subject; ?></td>
    <td><?php echo $data->message; ?></td>
    <td><?php echo Utilities::formatDate($data->created); ?></td>
    <td><?php echo $data->status; ?></td>
</tr>
