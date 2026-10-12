                </td>
                </tr>
                <tr>
                    <td class="email-footer" align="center" style="padding:28px 28px 4px;text-align:center;font-size:12px;line-height:1.8;color:#756e67;<?= email_style() ?>">
                        <p style="margin:0 0 18px;font-size:12px;line-height:1.8;<?= email_style() ?>">Contact us at <a href="mailto:<?= htmlspecialchars(business_contact_email, ENT_QUOTES, 'UTF-8') ?>" style="color:#f36b24;text-decoration:none;<?= email_style() ?>"><?= htmlspecialchars(business_contact_email, ENT_QUOTES, 'UTF-8') ?></a></p>
                        <p style="margin:0 0 16px;font-size:12px;line-height:1.8;<?= email_style() ?>">
                            <a class="email-footer-link" href="<?= production_url('privacy-policy') ?>" style="color:#625b54;text-decoration:none;<?= email_style() ?>">Privacy Policy</a>
                            &nbsp;·&nbsp;
                            <a class="email-footer-link" href="<?= production_url('terms-and-conditions') ?>" style="color:#625b54;text-decoration:none;<?= email_style() ?>">Terms and Conditions</a>
                            &nbsp;·&nbsp;
                            <a class="email-footer-link" href="<?= production_url('terms-of-use') ?>" style="color:#625b54;text-decoration:none;<?= email_style() ?>">Terms of Use</a>
                        </p>
                        <p class="email-reason" style="margin:0 0 8px;font-size:11px;line-height:1.8;<?= email_style() ?>"><?= email_receiving_reason($email_template ?? '') ?></p>
                        <p style="margin:0;font-size:11px;line-height:1.8;<?= email_style() ?>">&copy; <?= date('Y') ?> <?= business ?>. All rights reserved.<?php if (defined('business_address') && business_address !== '') { ?><br><?= business_address ?><?php } ?></p>
                    </td>
                </tr>
                </table>
                <!--[if mso]></td></tr></table><![endif]-->
                </td>
                </tr>
                </table>
                </body>

                </html>
