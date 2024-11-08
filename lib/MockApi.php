<?php

class MockStreamWrapper
{
    public $context;
    private $position;
    private $content;
    private $responses = [
        "https://api.site.com/book" => [
                                            ['message' => 'order successfully booked'],
                                            ['error' => 'barcode already exists'],
                                        ],
        "https://api.site.com/approve" => [
                                            ['message' => 'order successfully aproved'],
                                            ['error' => 'event cancelled'],
                                            ['error' => 'no tickets'],
                                            ['error' => 'no seats'],
                                            ['error' => 'fan removed'],
                                        ],
    ];

    // Открываем поток, определяя контент для замокированного URL
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->position = 0;

        $arrMes = $this->responses[$path];
        if ($path == "https://api.site.com/book") {
            $this->content = json_encode($arrMes[random_int(0,1)]);
        } elseif ($path == "https://api.site.com/approve") {
            $this->content = json_encode($arrMes[random_int(0,3)]);
        } else {
            $this->content = '{"error": "unknown endpoint"}';
        }
        return true;
    }

    // метод для чтения данных из потока
    public function stream_read($count)
    {
        $result = substr($this->content, $this->position, $count);
        $this->position += strlen($result);
        return $result;
    }
    // обязательный параметр
    public function stream_stat()
    {
        return [];
    }

    // проверка на конец файла
    public function stream_eof()
    {
        return $this->position >= strlen($this->content);
    }
}