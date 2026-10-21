<?php
$support_groups = array(
    array(
        'id' => 'pond-support-signin',
        'title' => 'Signing in & account recovery',
        'items' => array(
            array(
                'id' => 'pond-support-cant-login',
                'question' => 'I can\'t sign in to my account. What should I do?',
                'answer' => 'Sign in the same way you joined. If you signed up with Google or Facebook, use that button on the <a href="/login/">login page</a>. Those accounts don\'t have a separate Pond password. If you signed up with an email and password, enter the email address you used at checkout. If your password isn\'t accepted, use <a href="/login/?action=forgot_password">Forgot Password</a> to set a new one.',
            ),
            array(
                'id' => 'pond-support-reset-email',
                'question' => 'Forgot Password says it can\'t find my email, or the reset email never arrives.',
                'answer' => 'Make sure you\'re using the email address from your membership receipt. If your membership is active, Forgot Password will repair your sign-in account and send the reset link. Reset emails can take a few minutes, so check your spam, junk, and promotions folders too. If you still don\'t receive one, send us a message below with the email on your membership and we\'ll get you back in.',
            ),
            array(
                'id' => 'pond-support-in-app-browser',
                'question' => 'Google or Facebook sign-in isn\'t working from Instagram, Facebook, or another app.',
                'answer' => 'Browsers built into other apps often block Google and Facebook sign-in. Open the menu in that app, choose <strong>Open in browser</strong>, and sign in from Safari, Chrome, or another regular browser.',
            ),
            array(
                'id' => 'pond-support-change-login',
                'question' => 'How do I change my password or email address?',
                'answer' => 'To change your password, sign out and use <a href="/login/?action=forgot_password">Forgot Password</a>. That updates the password used to sign in. To change the email on your membership, send us a message below with your current and new email addresses. We\'ll update both your membership and your sign-in so they stay connected.',
            ),
        ),
    ),
    array(
        'id' => 'pond-support-billing',
        'title' => 'Billing & membership',
        'items' => array(
            array(
                'id' => 'pond-support-manage-billing',
                'question' => 'Where can I see my plan, receipts, or update my card?',
                'answer' => 'Sign in and go to <a href="/account/?action=subscriptions">Account &amp; Billing</a>. From there you can see your membership, its status, and your payment history, and update the card used for future payments.',
            ),
            array(
                'id' => 'pond-support-charges',
                'question' => 'When will I be charged?',
                'answer' => 'Monthly and yearly memberships renew automatically at the start of each billing period, using the card on file, until you cancel. A lifetime membership is a one-time payment with no renewals. If you see a charge you don\'t recognize, send us a message below and we\'ll look into it.',
            ),
            array(
                'id' => 'pond-support-cancel',
                'question' => 'How do I pause or cancel my membership?',
                'answer' => 'Go to <a href="/account/?action=subscriptions">Account &amp; Billing</a> and choose the option to pause or cancel your subscription, or send us a message below and we\'ll do it for you. If you cancel, you keep access until the end of the billing period you\'ve already paid for, and you won\'t be charged again.',
            ),
            array(
                'id' => 'pond-support-no-access',
                'question' => 'I paid, but I can\'t access the courses.',
                'answer' => 'Check that you\'re signed in with the same email address you used at checkout. Signing in with Google or Facebook under a different email creates a separate account without your membership. If the email matches and you still can\'t access courses, send us a message below with the email from your receipt.',
            ),
        ),
    ),
    array(
        'id' => 'pond-support-refunds',
        'title' => 'Refunds & guarantee',
        'items' => array(
            array(
                'id' => 'pond-support-guarantee',
                'question' => 'How does the 100% money-back guarantee work?',
                'answer' => 'Get better at hockey, or get your money back. If The Pond isn\'t helping your game, send us a message below with the email on your membership and we\'ll refund your payment to the original payment method.',
            ),
            array(
                'id' => 'pond-support-refund-timing',
                'question' => 'When will my refund show up?',
                'answer' => 'Once we\'ve processed your refund, your bank or card issuer usually takes 5–10 business days to show it on your statement. Refunds go back to the card or account you paid with.',
            ),
        ),
    ),
);
?>
<?php foreach ($support_groups as $group) : ?>
    <div class="pond-support-faq-group" aria-labelledby="<?php echo esc_attr($group['id']); ?>-title" role="group">
        <h3 id="<?php echo esc_attr($group['id']); ?>-title"><?php echo esc_html($group['title']); ?></h3>
        <?php get_template_part('template-parts/content-blocks/faq-accordion', null, array('items' => $group['items'])); ?>
    </div>
<?php endforeach; ?>
