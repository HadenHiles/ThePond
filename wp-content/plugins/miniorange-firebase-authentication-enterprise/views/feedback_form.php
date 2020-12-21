<?php


function mo_firebase_auth_display_feedback_form()
{
    if (!("\x70\154\165\147\x69\156\163\56\160\x68\160" != basename($_SERVER["\x50\x48\120\x5f\123\x45\x4c\106"]))) {
        goto SF;
    }
    return;
    SF:
    $k0 = array("\104\x6f\x65\163\x20\x6e\157\164\40\x68\141\166\x65\x20\164\150\x65\x20\x66\x65\141\164\x75\x72\x65\163\40\111\x27\x6d\40\x6c\x6f\x6f\x6b\x69\x6e\x67\x20\146\x6f\x72", "\103\157\156\x66\x75\x73\151\x6e\147\40\x49\156\x74\145\162\x66\141\143\145", "\102\165\x67\x73\x20\x69\156\x20\164\150\x65\40\160\x6c\165\x67\x69\x6e", "\x55\x6e\141\x62\x6c\x65\40\164\157\x20\x72\145\x67\151\163\164\x65\162", "\x4f\164\150\145\162\40\x52\x65\x61\x73\157\x6e\x73");
    wp_enqueue_style("\x77\x70\x2d\160\x6f\151\x6e\x74\x65\x72");
    wp_enqueue_script("\x77\x70\x2d\160\x6f\151\156\x74\x65\x72");
    wp_enqueue_script("\x75\x74\151\x6c\163");
    wp_enqueue_style("\x6d\x6f\x5f\x66\x69\162\x65\142\x61\163\145\x5f\141\x75\164\150\x5f\x61\x64\x6d\151\156\x5f\x73\x65\164\164\151\156\147\163\137\x73\164\x79\154\145", plugin_dir_url(dirname(__FILE__)) . "\x61\x64\155\151\156\57\143\163\x73\57\x73\164\171\x6c\145\x2e\143\x73\163");
    ?>
    </head>
    <body>
    <div id="firebase_auth_feedback_modal" class="mo_modal">
        <div class="mo_modal-content">
            <span class="mo_close">&times;</span>
            <h3>Tell us what happened? </h3>
            <form name="f" method="post" action="" id="mo_firebase_auth_feedback">
                <?php 
    wp_nonce_field("\155\157\x5f\x66\151\162\x65\142\x61\163\x65\137\x61\165\164\150\137\146\x65\x65\144\142\x61\x63\x6b\137\x66\x6f\x72\155", "\155\157\x5f\x66\151\x72\145\142\141\163\145\137\141\165\x74\x68\x5f\x66\145\145\144\142\141\143\x6b\x5f\146\x69\x65\x6c\144");
    ?>
                <input type="hidden" name="option" value="mo_firebase_auth_feedback"/>
                <div>
                    <p style="margin-left:2%">
				<?php 
    foreach ($k0 as $PP) {
        ?>
                    <div class="radio" style="padding:1px;margin-left:2%">
                        <label style="font-weight:normal;font-size:14.6px" for="<?php 
        echo $PP;
        ?>
">
                            <input type="radio" name="deactivate_reason_radio" value="<?php 
        echo $PP;
        ?>
"
                                   required>
							<?php 
        echo $PP;
        ?>
</label>
                    </div>
					<?php 
        aI:
    }
    yt:
    ?>
                    <br>
                    <textarea id="query_feedback" name="query_feedback" rows="4" style="margin-left:2%;width: 330px"
                              placeholder="Write your query here"></textarea>
                    <br><br>
                    <div class="mo_modal-footer">
                        <input type="submit" name="miniorange_feedback_submit"
                               class="button button-primary button-large" style="float: left;" value="Submit"/>
                        <input id="mo_skip" type="submit" name="miniorange_feedback_skip"
                               class="button button-primary button-large" style="float: right;" value="Skip"/>
                    </div>
                </div>
            </form>
            <form name="f" method="post" action="" id="mo_feedback_form_close">
                <input type="hidden" name="option" value="mo_firebase_auth_skip_feedback"/>
            </form>
        </div>
    </div>
    <script>
        jQuery('a[aria-label="Deactivate Firebase Authentication"]').click(function () {
            var mo_modal = document.getElementById('firebase_auth_feedback_modal');
            var mo_skip = document.getElementById('mo_skip');
            var span = document.getElementsByClassName("mo_close")[0];
            mo_modal.style.display = "block";
            jQuery('input:radio[name="deactivate_reason_radio"]').click(function () {
                var reason = jQuery(this).val();
                var query_feedback = jQuery('#query_feedback');
                query_feedback.removeAttr('required')

                if ( reason === "Does not have the features I'm looking for" ) {
                    query_feedback.attr( "placeholder", "Let us know what feature are you looking for" );
                    
                } else if ( reason === "Other Reasons:" ) {
                    query_feedback.attr( "placeholder", "Can you let us know the reason for deactivation" );
                    query_feedback.prop( 'required', true );

                } else if ( reason === "Bugs in the plugin" ) {
                    query_feedback.attr( "placeholder", "Can you please let us know about the bug in detail?" );

                } else if ( reason === "Confusing Interface" ) {
                    query_feedback.attr( "placeholder", "Finding it confusing? let us know so that we can improve the interface" );

                } else if ( reason === "Unable to register" ) {
                    query_feedback.attr( "placeholder", "Error while creating a new account? Can you please let us know the exact error?" );

                }


            });


            span.onclick = function () {
                mo_modal.style.display = "none";
                jQuery('#mo_feedback_form_close').submit();
            }
            mo_skip.onclick = function() {
                mo_modal.style.display = "none";
                jQuery('#mo_feedback_form_close').submit();
            }

            window.onclick = function (event) {
                if ( event.target == mo_modal ) {
                    mo_modal.style.display = "none";
                }
            }
            return false;

        });
    </script><?php 
}
?>
