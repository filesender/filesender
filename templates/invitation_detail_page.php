<?php

?>

<div class="fs-invitation-detail">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-invitation-detail__header">
                    <a id='fs-back-link' class="fs-link fs-link--circle">
                        <i class='fa fa-angle-left'></i>
                    </a>
                    <h1>Invitation details</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-7">
                <div class="fs-invitation-detail__information">
                    <h2>Invitation information</h2>
                    <div class="fs-info fs-info--aligned">
                        <strong>Transfer sent on:</strong>
                        <span>01/01/2023</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Expiration date:</strong>
                        <span>01/01/2023</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Message:</strong>
                        <span>Hi, please send me file.docx. Thank you very much.</span>
                    </div>
                    <div class="fs-info fs-info--aligned">
                        <strong>Language:</strong>
                        <span>English</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-12 col-lg-5">
                <div class="fs-invitation-detail__recipients">
                    <h2>Recipients</h2>
                    <p>
                        Your transfer was sent to the following email address:
                    </p>
                    <div>
                        <div class="fs-badge">
                            fulano@rnp.br
                        </div>
                    </div>
                    <ul class="fs-list fs-list--inline">
                        <li>
                            <button type="button" class="fs-button">
                                <i class="fa fa-mail-forward"></i>
                                <span>Send a reminder</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="fs-button">
                                <i class="fa fa-repeat"></i>
                                <span>Resend invitation</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-7">
                <div class="fs-invitation-detail__guest-list">
                    <h2>Guest transfer linked to this invitation</h2>
                    <table class="fs-table fs-table--responsive fs-table--selectable fs-table--thin fs-table--striped">
                        <thead>
                        <tr>
                            <th>
                                Transfer date
                            </th>
                            <th>
                                Size
                            </th>
                            <th>
                                Files
                            </th>
                            <th>
                                Guest transfers
                            </th>
                            <th>
                                Recipients
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="Transfer date">
                                March 8, 2023
                            </td>
                            <td data-label="Size">
                                20 MB
                            </td>
                            <td data-label="Files">
                                file.png; file.docx; file.pdf
                            </td>
                            <td data-label="Guest transfers">
                                + 3 more files
                            </td>
                            <td data-label="Recipients">
                                me
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-7">
                <div class="fs-invitation-detail__options">
                    <h2>Selected options for this transfer</h2>
                    <div class="row">
                        <div class="col col-sm-12 col-md-6">
                            <h3>Selected transfer options</h3>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-1">
                                        always include me as a recipient
                                    </label>
                                    <input id="check-1" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-2">
                                        redirect after download
                                    </label>
                                    <input id="check-2" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-3">
                                        recipient must login to download
                                    </label>
                                    <input id="check-3" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col col-sm-12 col-md-6">
                            <h3>Selected notification options</h3>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-4">
                                        email me the confirmation of this invitation
                                    </label>
                                    <input id="check-4" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-5">
                                        email me when guest accesses upload page
                                    </label>
                                    <input id="check-5" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-6">
                                        email me when guest starts upload
                                    </label>
                                    <input id="check-6" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-7">
                                        email me when upload is done
                                    </label>
                                    <input id="check-7" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-8">
                                        email me upon downloads of created transfer
                                    </label>
                                    <input id="check-8" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                            <div class="fs-invitation-detail__check">
                                <label class="fs-checkbox fs-checkbox--disabled">
                                    <label for="check-9">
                                        email me daily statistics of created transfer
                                    </label>
                                    <input id="check-9" type="checkbox" disabled>
                                    <span class="fs-checkbox__mark"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="fs-invitation-detail__actions">
                    <button type="button" class="fs-button fs-button--danger">
                        <i class="fa fa-trash"></i>
                        <span>Delete invitation</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{path:js/invitation_detail_page.js}"></script>
