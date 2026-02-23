<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Forgot password?</title>
</head>

<body>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title></title>
        
        <style type="text/css">
            @font-face {
                font-family: "flama-condensed";
                font-weight: 100;
                src: url("http://assets.vervewine.com/fonts/FlamaCond-Medium.eot");
                src: url("http://assets.vervewine.com/fonts/FlamaCond-Medium.eot?#iefix") format("embedded-opentype"), url("http://assets.vervewine.com/fonts/FlamaCond-Medium.woff") format("woff"),
                    url("http://assets.vervewine.com/fonts/FlamaCond-Medium.ttf") format("truetype");
            }

            @font-face {
                font-family: "Muli";
                font-weight: 100;
                src: url("http://assets.vervewine.com/fonts/muli-regular.eot");
                src: url("http://assets.vervewine.com/fonts/muli-regular.eot?#iefix") format("embedded-opentype"), url("http://assets.vervewine.com/fonts/muli-regular.woff2") format("woff2"),
                    url("http://assets.vervewine.com/fonts/muli-regular.woff") format("woff"), url("http://assets.vervewine.com/fonts/muli-regular.ttf") format("truetype");
            }

            .address-description a {
                color: #000000;
                text-decoration: none;
            }

            @media (max-device-width: 480px) {
                .vervelogoplaceholder {
                    height: 83px;
                }
            }
        </style>

    </head>

    <body bgcolor="#e1e5e8"
        style="margin-top: 0; margin-bottom: 0; margin-right: 0; margin-left: 0; padding-top: 0px; padding-bottom: 0px; padding-right: 0px; padding-left: 0px; background-color: #e1e5e8;">

        <center
            style="width: 100%; table-layout: fixed; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #e1e5e8;">
            <div style="max-width: 600px; margin-top: 0; margin-bottom: 0; margin-right: auto; margin-left: auto;">
                <table align="center" cellpadding="0"
                    style="border-spacing: 0; font-family: 'Muli', Arial, sans-serif; color: #333333; margin: 0 auto; width: 100%; max-width: 600px;">
                    <tbody>
                        <tr>
                            <td align="center" class="vervelogoplaceholder" height="143"
                                style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0; height: 143px; vertical-align: middle;"
                                valign="middle">
                                {{-- <span class="sg-image"
                                    data-imagelibrary="{{ asset('img/relync-diaspora-long.png') }}">
                                    <a href="#" target="_blank"><img height="100"
                                            src="{{ asset('img/relync-diaspora-long.png') }}"
                                            style="border-width: 0px; width: 200px; height: 100px;" width="160" /></a>
                                </span> --}}
                            </td>
                        </tr>
                        <tr>
                            <td class="one-column"
                                style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0; background-color: #ffffff;">
                                <table style="border-spacing: 0;" width="100%">
                                    <tbody>
                                        <tr>
                                            <td align="center" class="inner"
                                                style="padding-top: 15px; padding-bottom: 15px; padding-right: 30px; padding-left: 30px;"
                                                valign="middle">
                                                <span class="sg-image"
                                                    data-imagelibrary="%7B%22width%22%3A%22255%22%2C%22height%22%3A93%2C%22alt_text%22%3A%22Forgot%20Password%22%2C%22alignment%22%3A%22%22%2C%22border%22%3A0%2C%22src%22%3A%22https%3A//marketing-image-production.s3.amazonaws.com/uploads/35c763626fdef42b2197c1ef7f6a199115df7ff779f7c2d839bd5c6a8c2a6375e92a28a01737e4d72f42defcac337682878bf6b71a5403d2ff9dd39d431201db.png%22%2C%22classes%22%3A%7B%22sg-image%22%3A1%7D%7D">
                                                    <!-- <img alt="Forgot Password" class="banner" height="93" src="https://marketing-image-production.s3.amazonaws.com/uploads/35c763626fdef42b2197c1ef7f6a199115df7ff779f7c2d839bd5c6a8c2a6375e92a28a01737e4d72f42defcac337682878bf6b71a5403d2ff9dd39d431201db.png" style="border-width: 0px; margin-top: 30px; width: 255px; height: 93px;" width="255"> -->
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="inner contents center"
                                                style="padding-top: 15px; padding-bottom: 15px; padding-right: 30px; padding-left: 30px; text-align: left;">
                                                <center>
                                                    <p class="h1 center"
                                                        style="color:#000000; margin: 0; text-align: center; font-family: 'flama-condensed', 'Arial Narrow', Arial; font-weight: 100; font-size: 30px; margin-bottom: 26px;">
                                                        Forgot your password?
                                                    </p>
                                                    <p class="description center" style="
                                                                font-family: 'Muli', 'Arial Narrow', Arial;
                                                                margin: 0;
                                                                text-align: center;
                                                                max-width: 320px;
                                                                color: #a1a8ad;
                                                                line-height: 24px;
                                                                font-size: 15px;
                                                                margin-bottom: 10px;
                                                                margin-left: auto;
                                                                margin-right: auto;
                                                            ">
                                                        <span
                                                            style="color: rgb(161, 168, 173); font-family: Muli, 'Arial Narrow', Arial; font-size: 20px; text-align: center; background-color: rgb(255, 255, 255); font-weight: bold;">
                                                            Hello {{ $details['name'] }},
                                                        </span>
                                                    </p>
                                                    <p class="description center" style="
                                                                font-family: 'Muli', 'Arial Narrow', Arial;
                                                                margin: 0;
                                                                text-align: center;
                                                                max-width: 320px;
                                                                color: #a1a8ad;
                                                                line-height: 24px;
                                                                font-size: 15px;
                                                                margin-bottom: 10px;
                                                                margin-left: auto;
                                                                margin-right: auto;
                                                            ">
                                                        <span
                                                            style="color: rgb(161, 168, 173); font-family: Muli, 'Arial Narrow', Arial; font-size: 15px; text-align: center; background-color: rgb(255, 255, 255);">
                                                            That's okay, it happens! Below are your system generated
                                                            password,
                                                        </span>
                                                    </p>
                                                    <p class="description center" style="
                                                                font-family: 'Muli', 'Arial Narrow', Arial;
                                                                margin: 0;
                                                                text-align: center;
                                                                max-width: 320px;
                                                                color: #a1a8ad;
                                                                line-height: 24px;
                                                                font-size: 15px;
                                                                margin-bottom: 30px;
                                                                margin-left: auto;
                                                                margin-right: auto;
                                                            ">
                                                        <span
                                                            style="color: rgb(161, 168, 173); font-family: Muli, 'Arial Narrow', Arial; font-size: 15px; text-align: center; background-color: rgb(255, 255, 255); font-weight: bold;">
                                                            please change the password immediately after login.
                                                        </span>
                                                    </p>


                                                    
                                                    <p class="description center" >
                                                        <span
                                                            style="color: #ff9900; font-family: Muli, 'Arial Narrow', Arial; font-size: 45px; text-align: center; font-weight: bold; padding: 1px 40px;">{{ $details['password'] }}</span>
                                                    </p>

                                                    <p class="description center" style="
                                                                font-family: 'Muli', 'Arial Narrow', Arial;
                                                                margin: 0;
                                                                text-align: center;
                                                                max-width: 320px;
                                                                color: #a1a8ad;
                                                                line-height: 24px;
                                                                font-size: 15px;
                                                                margin-bottom: 10px;
                                                                margin-left: auto;
                                                                margin-right: auto;
                                                            ">
                                                        <a href="https://shelflifemanager.univerlab.it" style="
                                                                    font-family: 'Muli', 'Arial Narrow', Arial !important;
                                                                    background: rgb(33, 30, 30);
                                                                    color: white;
                                                                    border: unset;
                                                                    padding: 8px 35px;
                                                                    border-radius: 3px;
                                                                    font-family: auto;
                                                                    font-size: 18px;
                                                                    text-decoration: unset;
                                                                ">
                                                            Login To your account
                                                        </a>
                                                    </p>

                                                    {{-- <span class="sg-image"
                                                        data-imagelibrary="%7B%22width%22%3A%22260%22%2C%22height%22%3A54%2C%22alt_text%22%3A%22Reset%20your%20Password%22%2C%22alignment%22%3A%22%22%2C%22border%22%3A0%2C%22src%22%3A%22https%3A//marketing-image-production.s3.amazonaws.com/uploads/c1e9ad698cfb27be42ce2421c7d56cb405ef63eaa78c1db77cd79e02742dd1f35a277fc3e0dcad676976e72f02942b7c1709d933a77eacb048c92be49b0ec6f3.png%22%2C%22link%22%3A%22%23%22%2C%22classes%22%3A%7B%22sg-image%22%3A1%7D%7D">
                                                        <a href="#" target="_blank">
                                                            <!-- <img alt="Reset your Password" height="54" src="https://marketing-image-production.s3.amazonaws.com/uploads/c1e9ad698cfb27be42ce2421c7d56cb405ef63eaa78c1db77cd79e02742dd1f35a277fc3e0dcad676976e72f02942b7c1709d933a77eacb048c92be49b0ec6f3.png" style="border-width: 0px; margin-top: 30px; margin-bottom: 50px; width: 260px; height: 54px;" width="260"> -->
                                                        </a>
                                                    </span> --}}

                                                </center>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" class="inner"
                                                style="padding-top: 15px; padding-bottom: 15px; padding-right: 30px; padding-left: 30px;"
                                                valign="middle">
                                                {{-- <span class="sg-image"
                                                    data-imagelibrary="%7B%22width%22%3A%22255%22%2C%22height%22%3A93%2C%22alt_text%22%3A%22Forgot%20Password%22%2C%22alignment%22%3A%22%22%2C%22border%22%3A0%2C%22src%22%3A%22https%3A//marketing-image-production.s3.amazonaws.com/uploads/35c763626fdef42b2197c1ef7f6a199115df7ff779f7c2d839bd5c6a8c2a6375e92a28a01737e4d72f42defcac337682878bf6b71a5403d2ff9dd39d431201db.png%22%2C%22classes%22%3A%7B%22sg-image%22%3A1%7D%7D">
                                                    <!-- <img alt="Forgot Password" class="banner" height="93" src="https://marketing-image-production.s3.amazonaws.com/uploads/35c763626fdef42b2197c1ef7f6a199115df7ff779f7c2d839bd5c6a8c2a6375e92a28a01737e4d72f42defcac337682878bf6b71a5403d2ff9dd39d431201db.png" style="border-width: 0px; margin-top: 30px; width: 255px; height: 93px;" width="255"> -->
                                                </span> --}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </td>
                        </tr>
                        <!-- End of Email Body-->
                        <!-- whitespace -->
                        <tr>
                            <td height="40">
                                <p style="line-height: 40px; padding: 0 0 0 0; margin: 0 0 0 0;">&nbsp;</p>

                                <p>&nbsp;</p>
                            </td>
                        </tr>
                        <!-- Social Media -->




                    </tbody>
                </table>
            </div>
        </center>
    </body>
    <!-- partial -->
</body>

</html>
