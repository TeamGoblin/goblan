<?php

global $user;

?>
				<div class="row actionsBar">
					<div class="col-lg-3 col-3">
						<a href="/"><img src="/i/img/logo@2x.png" class="logo-content"/></a>
					</div>
					<div class="col-lg-6 center col-6"><ul class="inline actionLinks"><li><a href="/notes/sell">Sell Notes</a></li><li><a href="/notes/buy">Buy Notes<a/></li><li><a href="/">My Notes</a></li></ul></div>
					<div class="col-lg-3 right col-3"><ul class="inline actionLinks">
                            <?php
                            if (in_array('admin', $user->access))
                            {
                                echo "<li><a href='/admin_users'><i class='icon-home'></i></a></li>";
                            }
                            ?>
                            <li><a href="/user/edit"><i class="icon-cog"></i></a></li><li><a href="/user/logout"><i class="icon-off"></i></a></li></ul></div>
				</div>