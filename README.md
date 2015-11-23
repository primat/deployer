# deployer
Framework for deploying source code and databases for websites across multiple environments

This is a framework I built for deploying web applications between development 
environments at a previous job.

I orginally used Ant/Phing for deployments but it didn't have the features I 
wanted (i.e total control over I/O).

I also wrote it in Bash because the framework is essentially a wrapper over the 
console tools but writing applications in Bash is not fun.

Knowing PHP ver well, I finally decided to do it in PHP and it turned out OK. 
I now use it for all my projects unless Jenkins is more suitable.
