<?php

namespace GrantHolle\Notifications\Messages;

class AliyunMessage
{
    /**
     * The message template.
     *
     * @var string
     */
    public $template;

    /**
     * The message placeholders.
     *
     * @var array
     */
    public $data = [];

    /**
     * Create a new message instance.
     *
     * @param string $template
     * @param array $data
     */
    public function __construct(string $template = '', array $data = [])
    {
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * Set the template.
     *
     * @param string $template
     * @return $this
     */
    public function template(string $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Set the template placeholders.
     *
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = (string) $value;
        }

        return $this;
    }
}
