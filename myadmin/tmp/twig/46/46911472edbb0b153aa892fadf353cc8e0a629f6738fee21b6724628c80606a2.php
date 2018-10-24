<?php

/* display/results/value_display.twig */
class __TwigTemplate_5e248e26952a42407b3f034bb92ba9dea181c0c3d9d661f34ec2e9fddc2ead87 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<td class=\"left ";
        echo twig_escape_filter($this->env, ($context["class"] ?? null), "html", null, true);
        echo ((($context["condition_field"] ?? null)) ? (" condition") : (""));
        echo "\">
    ";
        // line 2
        echo ($context["value"] ?? null);
        echo "
</td>
";
    }

    public function getTemplateName()
    {
        return "display/results/value_display.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  25 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "display/results/value_display.twig", "/home/flewid/public_html/scribble.flewid.ca/htdocs/myadmin/templates/display/results/value_display.twig");
    }
}
