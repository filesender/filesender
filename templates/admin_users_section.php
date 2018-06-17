<h2>{tr:users}</h2>

<fieldset class="search">
    <legend>{tr:search_user}</legend>
    
    <input type="text" name="match" /> <input type="button" name="go" value="{tr:search}" />
</fieldset>

<table class="results">
    <tr>
        <th>{tr:user_id}</th>
        <th>{tr:last_activity}</th>
        <th>&nbsp;</th>
    </tr>
    
    <tr class="searching">
        <td colspan="3">{tr:searching}</td>
    </tr>
    <tr class="no_results">
        <td colspan="3">{tr:no_results}</td>
    </tr>
    
    <tr class="tpl">
        <td class="uid"></td>
        <td class="last_activity"></td>
        
        <td>
            <input type="button" data-action="show-client-logs" value="{tr:show_client_logs}" />
        </td>
    </tr>
</table>

<table class="client-logs">
    <tr>
        <th>{tr:date}</th>
        <th>{tr:message}</th>
    </tr>
    
    <tr class="searching">
        <td colspan="3">{tr:searching}</td>
    </tr>
    <tr class="no_results">
        <td colspan="2">{tr:no_results}</td>
    </tr>
    
    <tr class="tpl">
        <td class="date"></td>
        <td class="message"></td>
    </tr>
</table>

<script type="text/javascript" src="{path:js/admin_users.js}"></script>
